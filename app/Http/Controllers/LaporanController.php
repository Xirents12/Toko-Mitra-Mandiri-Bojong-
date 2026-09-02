<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\StokMasuk;
use App\Models\StokKeluar;
use App\Models\Transaksi;
use Carbon\Carbon;

class LaporanController extends Controller
{
    // Halaman utama laporan
    public function index()
    {
        return view('laporan.index');
    }

    // Laporan stok semua barang
    public function stok(Request $request)
    {
        $query = Barang::with('kategori');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_barang', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->kategori_id) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->status_stok === 'menipis') {
            $query->whereRaw('stok_saat_ini <= stok_minimum')->where('stok_saat_ini', '>', 0);
        } elseif ($request->status_stok === 'habis') {
            $query->where('stok_saat_ini', '<=', 0);
        } elseif ($request->status_stok === 'overstock') {
            $query->whereRaw('stok_saat_ini >= stok_maksimum')->where('stok_maksimum', '>', 0);
        } elseif ($request->status_stok === 'normal') {
            $query->whereRaw('stok_saat_ini > stok_minimum');
        }

        $barangs   = $query->orderBy('nama_barang')->get();
        $kategoris = \App\Models\Kategori::orderBy('nama_kategori')->get();

        return view('laporan.stok', compact('barangs', 'kategoris'));
    }

    // Laporan stok kritis
    public function stokKritis()
    {
        $barangs = Barang::with('kategori')
            ->whereRaw('stok_saat_ini <= stok_minimum')
            ->orderBy('stok_saat_ini', 'asc')
            ->get();

        return view('laporan.stok_kritis', compact('barangs'));
    }

    // Laporan mutasi stok
    public function mutasi(Request $request)
    {
        $tanggalAwal  = $request->tanggal_awal  ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? Carbon::now()->format('Y-m-d');

        $stokMasuks = StokMasuk::with(['details.barang', 'supplier', 'user'])
            ->whereBetween('tanggal_masuk', [$tanggalAwal, $tanggalAkhir])
            ->orderByDesc('tanggal_masuk')
            ->get();

        $stokKeluars = StokKeluar::with(['details.barang', 'user'])
            ->whereBetween('tanggal_keluar', [$tanggalAwal, $tanggalAkhir])
            ->orderByDesc('tanggal_keluar')
            ->get();

        return view('laporan.mutasi', compact('stokMasuks', 'stokKeluars', 'tanggalAwal', 'tanggalAkhir'));
    }

    // Laporan penjualan (berbasis transaksi kasir)
    public function penjualan(Request $request)
    {
        $tanggalAwal  = $request->tanggal_awal  ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? Carbon::now()->format('Y-m-d');

        $query = Transaksi::with(['detailTransaksi.barang', 'user'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

        if ($request->filled('kasir')) {
            $query->where('nama_kasir', $request->kasir);
        }
        if ($request->filled('metode')) {
            $query->where('metode_bayar', $request->metode);
        }

        $transaksis = $query->orderByDesc('tanggal')->orderByDesc('created_at')->get();

        // ── Ringkasan ──
        $totalTransaksi = $transaksis->count();
        $totalItem      = $transaksis->sum(fn ($t) => $t->detailTransaksi->sum('jumlah'));
        $totalPenjualan = $transaksis->sum('total_harga');

        // ── Rekap penjualan per barang (top 15) ──
        $rekapBarang = collect();
        foreach ($transaksis as $t) {
            foreach ($t->detailTransaksi as $d) {
                $key = $d->barang_id;

                $row = $rekapBarang->get($key, [
                    'kode'      => $d->barang->kode_barang ?? '-',
                    'nama'      => $d->barang->nama_barang ?? '-',
                    'satuan'    => $d->barang->satuan ?? '',
                    'qty'       => 0,
                    'penjualan' => 0,
                ]);

                $row['qty']       += $d->jumlah;
                $row['penjualan'] += $d->subtotal;

                $rekapBarang->put($key, $row);
            }
        }
        $rekapBarang = $rekapBarang->sortByDesc('penjualan')->take(15)->values();

        // ── Rekap per kasir ──
        $rekapKasir = $transaksis->groupBy('nama_kasir')->map(function ($g) {
            return [
                'kasir'      => $g->first()->nama_kasir ?? '-',
                'transaksi'  => $g->count(),
                'penjualan'  => $g->sum('total_harga'),
            ];
        })->sortByDesc('penjualan')->values();

        $kasirs  = Transaksi::select('nama_kasir')->distinct()->orderBy('nama_kasir')->pluck('nama_kasir');
        $metodes = ['tunai' => 'Tunai', 'kredit' => 'Kredit'];

        return view('laporan.penjualan', compact(
            'transaksis', 'rekapBarang', 'rekapKasir', 'kasirs', 'metodes',
            'totalTransaksi', 'totalItem', 'totalPenjualan',
            'tanggalAwal', 'tanggalAkhir'
        ));
    }

    // Cetak satu nota penjualan (per transaksi)
    public function cetakNota(Transaksi $transaksi)
    {
        $transaksi->load(['detailTransaksi.barang', 'user', 'piutang']);
        return view('laporan.cetak_nota', compact('transaksi'));
    }

    // Laporan klasifikasi per nota/invoice
    public function perNota(Request $request)
    {
        $tanggalAwal  = $request->tanggal_awal  ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? Carbon::now()->format('Y-m-d');

        // Ambang batas klasifikasi ukuran transaksi (bisa diubah lewat filter)
        $batasKecil = (int) ($request->batas_kecil  ?? 100000);
        $batasBesar = (int) ($request->batas_besar  ?? 500000);
        if ($batasBesar < $batasKecil) {
            $batasBesar = $batasKecil;
        }

        $query = Transaksi::with(['detailTransaksi.barang', 'user', 'piutang'])
            ->whereBetween('tanggal', [$tanggalAwal, $tanggalAkhir]);

        if ($request->filled('kasir')) {
            $query->where('nama_kasir', $request->kasir);
        }
        if ($request->filled('metode')) {
            $query->where('metode_bayar', $request->metode);
        }

        $transaksis = $query->orderByDesc('tanggal')->orderByDesc('created_at')->get();

        // Definisi klasifikasi status pembayaran + warna badge-nya
        $klasifikasiMap = [
            'Tunai'              => ['badge' => 'bg-success',            'label' => 'Tunai'],
            'Kredit Lunas'       => ['badge' => 'bg-info',               'label' => 'Kredit Lunas'],
            'Kredit Cicil'       => ['badge' => 'bg-warning text-dark',  'label' => 'Kredit Cicil'],
            'Kredit Belum Lunas' => ['badge' => 'bg-danger',             'label' => 'Kredit Belum Lunas'],
            'Kredit Macet'       => ['badge' => 'bg-dark',               'label' => 'Kredit Macet'],
        ];

        $ukuranMap = [
            'Kecil'  => 'bg-secondary',
            'Sedang' => 'bg-info',
            'Besar'  => 'bg-primary',
        ];

        // Hitung klasifikasi untuk setiap nota
        $items = $transaksis->map(function ($t) use ($batasKecil, $batasBesar) {
            $itemCount = $t->detailTransaksi->sum('jumlah');
            $returQty  = $t->detailTransaksi->sum('jumlah_diretur');

            $laba = $t->detailTransaksi->sum(function ($d) {
                $hargaBeli = $d->harga_beli ?? 0;
                return ($d->subtotal ?? 0) - (($d->jumlah ?? 0) * $hargaBeli);
            });

            // 1) Klasifikasi status pembayaran
            if ($t->metode_bayar === 'tunai') {
                $klasifikasi = 'Tunai';
            } else {
                $statusKredit  = $t->status_kredit;                  // lunas | cicil | belum_lunas | null
                $statusPiutang = $t->piutang->status ?? null;        // lunas | aktif | macet

                $klasifikasi = match (true) {
                    $statusKredit === 'lunas' || $statusPiutang === 'lunas'       => 'Kredit Lunas',
                    $statusKredit === 'cicil'                                       => 'Kredit Cicil',
                    $statusPiutang === 'macet'                                      => 'Kredit Macet',
                    $statusKredit === 'belum_lunas' || $statusPiutang === 'aktif'   => 'Kredit Belum Lunas',
                    default                                                         => 'Kredit Belum Lunas',
                };
            }

            // 2) Klasifikasi ukuran transaksi
            $total  = (int) $t->total_harga;
            $ukuran = $total < $batasKecil ? 'Kecil' : ($total >= $batasBesar ? 'Besar' : 'Sedang');

            return (object) [
                'id'           => $t->id,
                'no_invoice'   => $t->no_invoice ?? (string) $t->id,
                'tanggal'      => $t->tanggal,
                'kasir'        => $t->nama_kasir ?? '-',
                'pelanggan'    => $t->nama_pelanggan ?? '-',
                'metode'       => $t->metode_bayar,
                'klasifikasi'  => $klasifikasi,
                'ukuran'       => $ukuran,
                'retur'        => $returQty > 0,
                'item_count'   => $itemCount,
                'total'        => $t->total_harga,
                'laba'         => $laba,
                'detail'       => $t->detailTransaksi,
            ];
        });

        // ── Ringkasan ──
        $totalNota      = $items->count();
        $totalItem      = $items->sum('item_count');
        $totalPenjualan = $items->sum('total');
        $totalLaba      = $items->sum('laba');

        // ── Rekap per klasifikasi pembayaran ──
        $rekapKlasifikasi = $items->groupBy('klasifikasi')->map(function ($g) {
            return [
                'klasifikasi' => $g->first()->klasifikasi,
                'nota'        => $g->count(),
                'penjualan'   => $g->sum('total'),
                'laba'        => $g->sum('laba'),
            ];
        })->sortByDesc('penjualan')->values();

        // ── Rekap per ukuran transaksi (urutan Kecil, Sedang, Besar) ──
        $urutanUkuran = ['Kecil', 'Sedang', 'Besar'];
        $rekapUkuran = $items->groupBy('ukuran')->map(function ($g) {
            return [
                'ukuran'    => $g->first()->ukuran,
                'nota'      => $g->count(),
                'penjualan' => $g->sum('total'),
                'laba'      => $g->sum('laba'),
            ];
        })->sortBy(fn ($r) => array_search($r['ukuran'], $urutanUkuran))->values();

        $kasirs  = Transaksi::select('nama_kasir')->distinct()->orderBy('nama_kasir')->pluck('nama_kasir');
        $metodes = ['tunai' => 'Tunai', 'kredit' => 'Kredit'];

        return view('laporan.per_nota', compact(
            'items', 'rekapKlasifikasi', 'rekapUkuran', 'kasirs', 'metodes',
            'klasifikasiMap', 'ukuranMap',
            'totalNota', 'totalItem', 'totalPenjualan', 'totalLaba',
            'tanggalAwal', 'tanggalAkhir', 'batasKecil', 'batasBesar'
        ));
    }

    // Laporan stok overstok (stok >= stok_maksimum)
    public function overstok()
    {
        $barangs = Barang::with('kategori')
            ->where('is_active', true)
            ->whereRaw('stok_saat_ini >= stok_maksimum')
            ->where('stok_maksimum', '>', 0)
            ->orderByDesc('stok_saat_ini')
            ->get();

        return view('laporan.overstok', compact('barangs'));
    }

    // Riwayat mutasi stok otomatis (log per barang)
    public function mutasiDetail(Request $request)
    {
        $tanggalAwal  = $request->tanggal_awal  ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $tanggalAkhir = $request->tanggal_akhir ?? Carbon::now()->format('Y-m-d');

        $query = MutasiStok::with(['barang', 'user'])
            ->whereBetween('created_at', [$tanggalAwal . ' 00:00:00', $tanggalAkhir . ' 23:59:59']);

        if ($request->filled('tipe')) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('search')) {
            $query->whereHas('barang', fn ($q) => $q->where('nama_barang', 'like', '%' . $request->search . '%'));
        }

        $mutasis = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('laporan.mutasi_detail', compact('mutasis', 'tanggalAwal', 'tanggalAkhir'));
    }
}