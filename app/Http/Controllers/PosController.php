<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\MutasiStok;
use App\Models\Piutang;
use App\Models\Cicilan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PosController extends Controller
{
    public function index()
    {
        $barangs = Barang::where('is_active', true)
            ->where('stok_saat_ini', '>', 0)
            ->with('kategori', 'preferredSupplier')
            ->orderBy('nama_barang')
            ->get();

        return view('pos.index', compact('barangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items'          => 'required|array|min:1',
            'items.*.id'     => 'required|exists:barangs,id',
            'items.*.jumlah' => 'required|integer|min:1',
            'bayar'          => 'required|integer|min:0',
            'nama_kasir'     => 'nullable|string|max:255',
            'nama_pelanggan' => 'nullable|string|max:255',
            'metode_bayar'   => 'required|in:tunai,kredit',
            // min:0 → 0 artinya tunai (JS mengirim 0 saat bukan kredit)
            'dp'             => 'nullable|integer|min:0',
            'max_cicilan'    => 'nullable|integer|min:0|max:5',
            'tenor_bulan'    => 'nullable|integer|min:1|max:3',
            'no_telepon'     => 'nullable|string|max:20',
            'alamat'         => 'nullable|string',
        ]);

        if ($request->metode_bayar === 'kredit' && !$request->nama_pelanggan) {
            return back()->with('error', 'Nama pelanggan wajib diisi untuk pembayaran kredit.');
        }

        // ── Siapkan data & cek stok SEBELUM menulis apa pun ke database ──
        $total = 0;
        $details = [];

        foreach ($request->items as $item) {
            $barang = Barang::findOrFail($item['id']);

            if ($barang->stok_saat_ini < $item['jumlah']) {
                return back()->with('error', "Stok {$barang->nama_barang} tidak cukup.");
            }

            $subtotal = $barang->harga_jual * $item['jumlah'];
            $total += $subtotal;

            $details[] = [
                'barang'       => $barang,
                'barang_id'    => $barang->id,
                'jumlah'       => $item['jumlah'],
                'harga_satuan' => $barang->harga_jual,
                'subtotal'     => $subtotal,
            ];
        }

        // Validasi pembayaran tunai di sisi server (pengaman jika JS dimatikan)
        if ($request->metode_bayar === 'tunai' && $request->bayar < $total) {
            return back()->with('error', 'Nominal bayar kurang dari total transaksi (Rp ' . number_format($total, 0, ',', '.') . ').');
        }

        // Uang muka (DP) untuk kredit — tidak boleh melunasi seluruhnya (pakai Tunai jika begitu)
        $dp = $request->metode_bayar === 'kredit' ? (int) ($request->dp ?? 0) : 0;
        if ($request->metode_bayar === 'kredit' && $dp >= $total) {
            return back()->with('error', 'Uang muka (DP) tidak boleh sama dengan atau melebihi total transaksi (Rp ' . number_format($total, 0, ',', '.') . '). Jika ingin melunasi, gunakan metode Tunai.');
        }

        DB::beginTransaction();

        try {
            // Kurangi stok
            foreach ($details as $detail) {
                $detail['barang']->decrement('stok_saat_ini', $detail['jumlah']);
            }

            // Generate no_invoice
            $noInvoice = Transaksi::generateNoInvoice();

            $transaksi = Transaksi::create([
                'user_id'        => Auth::id(),
                'nama_kasir'     => $request->nama_kasir ?: Auth::user()->name,
                'tanggal'        => now()->toDateString(),
                'no_invoice'     => $noInvoice,
                'total_harga'    => $total,
                'metode_bayar'   => $request->metode_bayar,
                'status_kredit'  => $request->metode_bayar === 'kredit' ? 'belum_lunas' : null,
                'nama_pelanggan' => $request->nama_pelanggan,
                'bayar'          => $request->metode_bayar === 'kredit' ? $dp : $request->bayar,
                'kembalian'      => $request->metode_bayar === 'kredit' ? 0 : ($request->bayar - $total),
            ]);

            foreach ($details as $detail) {
                DetailTransaksi::create([
                    'transaksi_id' => $transaksi->id,
                    'barang_id'    => $detail['barang_id'],
                    'jumlah'       => $detail['jumlah'],
                    'harga_satuan' => $detail['harga_satuan'],
                    'harga_beli'   => $detail['barang']->harga_beli,
                    'subtotal'     => $detail['subtotal'],
                ]);

                // Catat mutasi stok keluar (penjualan)
                MutasiStok::catat($detail['barang_id'], 'keluar', $detail['jumlah'], 'Penjualan ' . $noInvoice);
            }

            // Jika kredit, buat piutang (total piutang = total transaksi dikurangi uang muka/DP)
            if ($request->metode_bayar === 'kredit') {
                $maxCicilan = max(1, min((int) $request->max_cicilan, 5));
                // Tenor dalam bulan, maksimal 3 bulan → menentukan tanggal jatuh tempo
                $tenorBulan = max(1, min((int) ($request->tenor_bulan ?: 3), 3));
                $jatuhTempo = now()->addMonths($tenorBulan);
                $sisaPiutang = $total - $dp;
                $besarCicilan = $sisaPiutang / $maxCicilan;

                $piutang = Piutang::create([
                    'transaksi_id'       => $transaksi->id,
                    'user_id'            => Auth::id(),
                    'nama_pelanggan'     => $request->nama_pelanggan,
                    'no_telepon'         => $request->no_telepon ?? '',
                    'alamat'            => $request->alamat ?? '',
                    'total_piutang'      => $sisaPiutang,
                    'sisa_piutang'       => $sisaPiutang,
                    'max_cicilan'        => $maxCicilan,
                    'tenor_bulan'        => $tenorBulan,
                    'jml_cicilan_terbayar' => 0,
                    'besar_cicilan'      => $besarCicilan,
                    'tanggal_jatuh_tempo' => $jatuhTempo,
                    'status'             => 'aktif',
                ]);

                // Catat uang muka (DP) sebagai pembayaran awal agar riwayat pembayaran lengkap
                if ($dp > 0) {
                    Cicilan::create([
                        'piutang_id'    => $piutang->id,
                        'jumlah'        => $dp,
                        'tanggal_bayar' => now()->toDateString(),
                        'metode_bayar'  => 'tunai',
                        'keterangan'    => 'Uang Muka (DP)',
                        'user_id'       => Auth::id(),
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('pos.struk', $transaksi->id);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Transaksi gagal: ' . $e->getMessage());
        }
    }

    public function struk(Transaksi $transaksi)
    {
        $transaksi->load('detailTransaksi.barang', 'user', 'piutang');
        return view('pos.struk', compact('transaksi'));
    }

    // Riwayat transaksi untuk kasir (filter tanggal/bulan/tahun + pencarian invoice/pelanggan)
    public function riwayat(Request $request)
    {
        $request->validate([
            'search'  => 'nullable|string|max:100',
            'tanggal' => 'nullable|date',
            'bulan'   => 'nullable|integer|between:1,12',
            'tahun'   => 'nullable|integer|digits:4',
        ]);

        $query = Transaksi::with(['user', 'detailTransaksi']);

        $adaPencarian = $request->filled('search');
        $adaTanggal   = $request->filled('tanggal');
        $adaPeriode   = $request->filled('bulan') || $request->filled('tahun');

        // Cari berdasarkan no. invoice atau nama pelanggan (semua tanggal)
        if ($adaPencarian) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('no_invoice', 'like', '%' . $search . '%')
                  ->orWhere('nama_pelanggan', 'like', '%' . $search . '%');
            });
        }

        // Prioritas 1: tanggal spesifik
        if ($adaTanggal) {
            $query->whereDate('created_at', $request->tanggal);
        }
        // Prioritas 2: bulan &/atau tahun
        elseif ($adaPeriode) {
            if ($request->filled('bulan')) {
                $query->whereMonth('created_at', (int) $request->bulan);
            }
            if ($request->filled('tahun')) {
                $query->whereYear('created_at', (int) $request->tahun);
            }
        }
        // Tanpa pencarian & tanpa filter tanggal/periode → transaksi hari ini
        elseif (!$adaPencarian) {
            $query->whereDate('created_at', today());
        }

        $transaksis = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        // Daftar tahun yang tersedia di data transaksi (untuk dropdown)
        $tahunTersedia = Transaksi::selectRaw('YEAR(created_at) as tahun')
            ->groupBy('tahun')
            ->orderByDesc('tahun')
            ->pluck('tahun');

        // Nama bulan Indonesia
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // Label periode aktif untuk judul
        if ($adaTanggal) {
            $labelPeriode = \Carbon\Carbon::parse($request->tanggal)->format('d/m/Y');
        } elseif ($adaPeriode) {
            $labelPeriode = trim(
                ($request->filled('bulan') ? ($bulanList[(int) $request->bulan] ?? '') : '') . ' '
                . ($request->filled('tahun') ? $request->tahun : '')
            ) ?: 'Semua Tanggal';
        } elseif ($adaPencarian) {
            $labelPeriode = 'Semua Tanggal';
        } else {
            $labelPeriode = 'Hari Ini';
        }

        // Ringkasan hasil filter (total transaksi & total nilai)
        $semua          = (clone $query)->get();
        $totalTransaksi = $semua->count();
        $totalNilai     = $semua->sum('total_harga');

        return view('pos.riwayat', compact(
            'transaksis', 'tahunTersedia', 'bulanList', 'labelPeriode', 'totalTransaksi', 'totalNilai'
        ));
    }
}
