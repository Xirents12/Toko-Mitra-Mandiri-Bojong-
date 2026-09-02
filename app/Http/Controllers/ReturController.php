<?php

namespace App\Http\Controllers;

use App\Models\Transaksi;
use App\Models\DetailTransaksi;
use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\StokKeluar;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index()
    {
        $transaksis = Transaksi::with(['user', 'detailTransaksi.barang'])
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->get();

        $returned = $this->getReturnedInvoices();
        $selesai  = $this->getSelesaiReturInvoices($transaksis);

        $data = compact('transaksis', 'returned', 'selesai');

        // Data retur ke supplier hanya dimuat untuk Bagian Gudang
        if (Auth::user()->isGudang()) {
            $data = array_merge($data, $this->getReturSupplierData(), $this->getStokMasukData());
        }

        return view('retur.index', $data);
    }

    /**
     * Invoice yang SELURUH itemnya sudah diretur penuh (tidak bisa diretur lagi).
     */
    private function getSelesaiReturInvoices($transaksis): array
    {
        return $transaksis
            ->filter(fn ($t) => $t->detailTransaksi->isNotEmpty()
                && $t->detailTransaksi->every(fn ($d) => $d->sisaRetur <= 0))
            ->map(fn ($t) => $t->no_invoice ?? (string) $t->id)
            ->values()
            ->all();
    }

    /**
     * Daftar invoice yang sudah pernah diproses retur (diambil dari keterangan stok masuk retur).
     */
    private function getReturnedInvoices()
    {
        return StokMasuk::where('keterangan', 'like', 'Retur dari invoice:%')
            ->pluck('keterangan')
            ->map(function ($k) {
                preg_match('/Retur dari invoice: (.+?) - Alasan:/', $k, $m);
                return trim($m[1] ?? '');
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Ringkasan & data terbaru retur ke supplier (StokKeluar jenis "retur").
     */
    private function getReturSupplierData(): array
    {
        $query = StokKeluar::where('jenis_keluar', 'retur');

        $totalTransaksi = (clone $query)->count();
        $semua = (clone $query)->with('details')->get();
        $totalQty   = $semua->sum(fn ($r) => $r->details->sum('jumlah'));
        $totalNilai = $semua->sum(fn ($r) => $r->total);

        $terbaru = (clone $query)
            ->with(['supplier', 'user', 'details.barang'])
            ->latest()
            ->limit(10)
            ->get();

        return array_merge(
            compact('totalTransaksi', 'totalQty', 'totalNilai', 'terbaru'),
            ['jumlahReturSupplier' => $totalTransaksi]
        );
    }

    /**
     * Daftar BARANG yang berasal dari supplier untuk tab "Retur ke Supplier".
     * Hanya barang yang pernah diterima lewat nota stok masuk dari supplier
     * dan masih punya sisa yang bisa diretur (jumlah_diretur < jumlah).
     * Supplier tujuan otomatis diambil dari nota stok masuk TERAKHIR barang diterima.
     */
    private function getStokMasukData(): array
    {
        $barangSuppliers = Barang::where('is_active', true)
            ->where('stok_saat_ini', '>', 0)
            ->orderBy('nama_barang')
            ->get()
            ->map(function ($barang) {
                $detail = StokMasukDetail::with(['stokMasuk.supplier'])
                    ->where('barang_id', $barang->id)
                    ->whereHas('stokMasuk', fn ($q) => $q->whereNotNull('supplier_id'))
                    ->whereColumn('jumlah_diretur', '<', 'jumlah')
                    ->latest('stok_masuk_id')
                    ->first();

                if (!$detail || !$detail->stokMasuk->supplier) {
                    return null;
                }

                return (object) [
                    'barang'   => $barang,
                    'nota'     => $detail->stokMasuk,
                    'supplier' => $detail->stokMasuk->supplier,
                    'no_nota'  => $detail->stokMasuk->no_transaksi,
                    'sisa'     => $detail->sisaRetur,
                ];
            })
            ->filter()
            ->values();

        return compact('barangSuppliers');
    }

    /**
     * Daftar no. nota stok masuk yang sudah pernah diproses retur ke supplier
     * (diambil dari keterangan stok keluar retur).
     */
    private function getReturnedNota(): array
    {
        return StokKeluar::where('jenis_keluar', 'retur')
            ->where('keterangan', 'like', 'Retur dari nota:%')
            ->pluck('keterangan')
            ->map(function ($k) {
                preg_match('/Retur dari nota: (.+?) - Alasan:/', $k, $m);
                return trim($m[1] ?? '');
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function cariTransaksi(Request $request)
    {
        $request->validate([
            'no_invoice' => 'required|string'
        ]);

        $transaksi = Transaksi::with(['detailTransaksi.barang', 'user'])
            ->where('no_invoice', $request->no_invoice)
            ->first();

        if (!$transaksi) {
            return back()->with('error', 'Transaksi dengan invoice tersebut tidak ditemukan.');
        }

        $returned = $this->getReturnedInvoices();

        $selesai = ($transaksi->detailTransaksi->isNotEmpty()
            && $transaksi->detailTransaksi->every(fn ($d) => $d->sisaRetur <= 0))
                ? [$transaksi->no_invoice ?? (string) $transaksi->id]
                : [];

        $data = compact('transaksi', 'returned', 'selesai');

        // Data retur ke supplier hanya dimuat untuk Bagian Gudang
        if (Auth::user()->isGudang()) {
            $data = array_merge($data, $this->getReturSupplierData(), $this->getStokMasukData());
        }

        return view('retur.index', $data);
    }

    public function formRetur(Transaksi $transaksi)
    {
        $transaksi->load(['detailTransaksi.barang', 'user']);
        return view('retur.form', compact('transaksi'));
    }

    public function prosesRetur(Request $request, Transaksi $transaksi)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:transaksi_details,id',
            'items.*.jumlah_kembali' => 'required|integer|min:0',
            'items.*.alasan' => 'nullable|in:Barang Rusak,Barang Tidak Sesuai,Barang Tidak Terpakai,Lainnya',
            'items.*.alasan_lainnya' => 'nullable|string|max:255',
        ], [
            'items.required' => 'Pilih minimal 1 barang dengan jumlah retur lebih dari 0.',
            'items.min' => 'Pilih minimal 1 barang dengan jumlah retur lebih dari 0.',
            'items.array' => 'Data retur tidak valid.',
            'items.*.detail_id.required' => 'Data barang retur tidak valid.',
            'items.*.detail_id.exists' => 'Data barang retur tidak valid.',
            'items.*.jumlah_kembali.required' => 'Jumlah retur wajib diisi.',
            'items.*.jumlah_kembali.integer' => 'Jumlah retur harus berupa angka.',
            'items.*.jumlah_kembali.min' => 'Jumlah retur tidak boleh kurang dari 0.',
            'items.*.alasan.in' => 'Alasan retur tidak valid.',
            'items.*.alasan_lainnya.max' => 'Keterangan alasan lainnya terlalu panjang (maks. 255 karakter).',
        ]);

        // Siapkan data retur per barang dengan alasan masing-masing
        $returItems = [];
        foreach ($request->items as $item) {
            $detail = DetailTransaksi::find($item['detail_id']);

            if (!$detail || $detail->transaksi_id !== $transaksi->id) {
                return back()->with('error', 'Data tidak valid.');
            }

            // Jumlah retur tidak boleh melebihi sisa yang belum diretur
            $sisa    = $detail->sisaRetur;
            $diminta = (int) $item['jumlah_kembali'];
            if ($diminta > $sisa) {
                return back()->with('error', 'Jumlah retur untuk ' . $detail->barang->nama_barang
                    . ' melebihi sisa yang belum diretur (sisa: ' . $sisa . ' ' . $detail->barang->satuan . ').');
            }

            $jmlRetur = $diminta;
            if ($jmlRetur <= 0) continue;

            // Alasan wajib diisi untuk barang yang diretur
            $alasan = $item['alasan'] ?? null;
            if (!$alasan) {
                return back()->with('error', 'Pilih alasan retur untuk setiap barang yang diretur.');
            }

            // Alasan "Lainnya" memerlukan keterangan khusus
            if ($alasan === 'Lainnya') {
                $alasan = trim($item['alasan_lainnya'] ?? '');
                if ($alasan === '') {
                    return back()->with('error', 'Tuliskan alasan retur pada kolom "Alasan Lainnya".');
                }
            }

            $returItems[] = [
                'detail' => $detail,
                'jumlah' => $jmlRetur,
                'alasan' => $alasan,
            ];
        }

        if (count($returItems) === 0) {
            return back()->with('error', 'Pilih minimal 1 barang dengan jumlah retur lebih dari 0.');
        }

        // Ringkasan alasan untuk keterangan header (dibatasi agar tidak melebihi panjang kolom)
        $alasanList = array_values(array_unique(array_column($returItems, 'alasan')));
        $alasanRingkasan = count($alasanList) > 3
            ? implode(', ', array_slice($alasanList, 0, 3)) . ', dll.'
            : implode(', ', $alasanList);

        DB::beginTransaction();

        try {
            // 1 StokMasuk untuk 1 transaksi retur
            $stokMasuk = StokMasuk::create([
                'no_transaksi' => 'RT-' . date('YmdHis') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT),
                'tanggal_masuk' => now()->toDateString(),
                'keterangan' => 'Retur dari invoice: ' . ($transaksi->no_invoice ?? $transaksi->id) . ' - Alasan: ' . $alasanRingkasan,
                'user_id' => Auth::id(),
            ]);

            foreach ($returItems as $ri) {
                // Kembalikan stok barang
                Barang::where('id', $ri['detail']->barang_id)
                    ->increment('stok_saat_ini', $ri['jumlah']);

                // Catat detail retur ke stok_masuk_details dengan alasan per barang
                StokMasukDetail::create([
                    'stok_masuk_id' => $stokMasuk->id,
                    'barang_id' => $ri['detail']->barang_id,
                    'jumlah' => $ri['jumlah'],
                    'harga_beli' => $ri['detail']->barang->harga_beli,
                    'keterangan' => 'Retur dari invoice: ' . ($transaksi->no_invoice ?? $transaksi->id) . ' - Alasan: ' . $ri['alasan'],
                ]);

                // Catat mutasi stok masuk (retur)
                MutasiStok::catat($ri['detail']->barang_id, 'masuk', $ri['jumlah'], 'Retur ' . ($stokMasuk->no_transaksi ?? '') . ' (' . $ri['alasan'] . ')');

                // Kurangi sisa retur pada detail transaksi
                DetailTransaksi::where('id', $ri['detail']->id)
                    ->increment('jumlah_diretur', $ri['jumlah']);
            }

            DB::commit();

            return redirect()->route('retur.index')
                ->with('success', 'Retur berhasil diproses. Stok telah dikembalikan.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Retur gagal: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    //  RETUR KE SUPPLIER (berbasis nota stok masuk → stok keluar)
    // ═══════════════════════════════════════════════════════════════

    public function returSupplier()
    {
        return redirect()->route('retur.index', ['tab' => 'supplier']);
    }

    public function cariStokMasuk(Request $request)
    {
        $request->validate([
            'no_nota' => 'required|string'
        ]);

        $stokMasuk = StokMasuk::with(['supplier', 'user', 'details.barang'])
            ->where('no_transaksi', trim($request->no_nota))
            ->where('keterangan', 'not like', 'Retur dari invoice:%')
            ->whereNotNull('supplier_id')
            ->first();

        if (!$stokMasuk) {
            return back()->with('error', 'Nota stok masuk dengan nomor tersebut tidak ditemukan.');
        }

        $returnedNota = $this->getReturnedNota();

        $selesaiNota = ($stokMasuk->details->isNotEmpty()
            && $stokMasuk->details->every(fn ($d) => $d->sisaRetur <= 0))
                ? [$stokMasuk->no_transaksi]
                : [];

        $data = compact('stokMasuk', 'returnedNota', 'selesaiNota');
        $data = array_merge($data, $this->getReturSupplierData(), $this->getStokMasukData());

        return view('retur.index', $data);
    }

    public function formReturSupplier(StokMasuk $stokMasuk)
    {
        $stokMasuk->load(['supplier', 'user', 'details.barang']);
        return view('retur.supplier_form', compact('stokMasuk'));
    }

    public function prosesReturSupplier(Request $request, StokMasuk $stokMasuk)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:stok_masuk_details,id',
            'items.*.jumlah_kembali' => 'required|integer|min:0',
            'items.*.alasan' => 'nullable|in:Barang Rusak,Salah Kirim / Tidak Sesuai,Kadaluarsa / Expired,Barang Tidak Laku,Lainnya',
            'items.*.alasan_lainnya' => 'nullable|string|max:255',
        ], [
            'items.required' => 'Pilih minimal 1 barang dengan jumlah retur lebih dari 0.',
            'items.min' => 'Pilih minimal 1 barang dengan jumlah retur lebih dari 0.',
            'items.array' => 'Data retur tidak valid.',
            'items.*.detail_id.required' => 'Data barang retur tidak valid.',
            'items.*.detail_id.exists' => 'Data barang retur tidak valid.',
            'items.*.jumlah_kembali.required' => 'Jumlah retur wajib diisi.',
            'items.*.jumlah_kembali.integer' => 'Jumlah retur harus berupa angka.',
            'items.*.jumlah_kembali.min' => 'Jumlah retur tidak boleh kurang dari 0.',
            'items.*.alasan.in' => 'Alasan retur tidak valid.',
            'items.*.alasan_lainnya.max' => 'Keterangan alasan lainnya terlalu panjang (maks. 255 karakter).',
        ]);

        // Siapkan data retur per barang dengan alasan masing-masing
        $returItems = [];
        foreach ($request->items as $item) {
            $detail = StokMasukDetail::with('barang')->find($item['detail_id']);

            if (!$detail || $detail->stok_masuk_id !== $stokMasuk->id) {
                return back()->with('error', 'Data tidak valid.');
            }

            // Jumlah retur tidak boleh melebihi sisa yang belum diretur
            $sisa    = $detail->sisaRetur;
            $diminta = (int) $item['jumlah_kembali'];
            if ($diminta > $sisa) {
                return back()->with('error', 'Jumlah retur untuk ' . $detail->barang->nama_barang
                    . ' melebihi sisa yang belum diretur (sisa: ' . $sisa . ' ' . $detail->barang->satuan . ').');
            }

            $jmlRetur = $diminta;
            if ($jmlRetur <= 0) continue;

            // Alasan wajib diisi untuk barang yang diretur
            $alasan = $item['alasan'] ?? null;
            if (!$alasan) {
                return back()->with('error', 'Pilih alasan retur untuk setiap barang yang diretur.');
            }

            // Alasan "Lainnya" memerlukan keterangan khusus
            if ($alasan === 'Lainnya') {
                $alasan = trim($item['alasan_lainnya'] ?? '');
                if ($alasan === '') {
                    return back()->with('error', 'Tuliskan alasan retur pada kolom "Alasan Lainnya".');
                }
            }

            // Cek stok mencukupi
            if ($detail->barang->stok_saat_ini < $jmlRetur) {
                return back()->with('error', 'Stok ' . $detail->barang->nama_barang
                    . ' tidak mencukupi. Tersedia: ' . $detail->barang->stok_saat_ini
                    . ' ' . $detail->barang->satuan . '.');
            }

            $returItems[] = [
                'detail' => $detail,
                'jumlah' => $jmlRetur,
                'alasan' => $alasan,
            ];
        }

        if (count($returItems) === 0) {
            return back()->with('error', 'Pilih minimal 1 barang dengan jumlah retur lebih dari 0.');
        }

        // Ringkasan alasan untuk keterangan header (dibatasi agar tidak melebihi panjang kolom)
        $alasanList = array_values(array_unique(array_column($returItems, 'alasan')));
        $alasanRingkasan = count($alasanList) > 3
            ? implode(', ', array_slice($alasanList, 0, 3)) . ', dll.'
            : implode(', ', $alasanList);

        $supplier = $stokMasuk->supplier;

        DB::beginTransaction();

        try {
            // 1 StokKeluar retur untuk 1 nota stok masuk
            $stokKeluar = StokKeluar::create([
                'no_transaksi'   => StokKeluar::generateNoReturSupplier(),
                'tanggal_keluar' => now()->toDateString(),
                'nama_pelanggan' => null,
                'jenis_keluar'   => 'retur',
                'supplier_id'    => $supplier->id ?? null,
                'keterangan'     => 'Retur dari nota: ' . $stokMasuk->no_transaksi
                    . ' - Alasan: ' . $alasanRingkasan,
                'user_id'        => Auth::id(),
            ]);

            foreach ($returItems as $ri) {
                $stokKeluar->details()->create([
                    'barang_id'  => $ri['detail']->barang_id,
                    'jumlah'     => $ri['jumlah'],
                    'harga_beli' => $ri['detail']->harga_beli,
                    'keterangan' => 'Retur dari nota: ' . $stokMasuk->no_transaksi
                        . ' - Alasan: ' . $ri['alasan'],
                ]);

                // Kurangi stok barang
                Barang::where('id', $ri['detail']->barang_id)
                    ->decrement('stok_saat_ini', $ri['jumlah']);

                // Catat mutasi stok keluar (retur)
                MutasiStok::catat($ri['detail']->barang_id, 'keluar', $ri['jumlah'],
                    'Retur ke supplier ' . $stokKeluar->no_transaksi . ' (' . $ri['alasan'] . ')');

                // Kurangi sisa retur pada detail nota stok masuk
                StokMasukDetail::where('id', $ri['detail']->id)
                    ->increment('jumlah_diretur', $ri['jumlah']);
            }

            DB::commit();

            return redirect()->route('retur.supplier-riwayat')
                ->with('success', 'Retur ke supplier berhasil diproses. Stok telah dikurangi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Retur gagal: ' . $e->getMessage());
        }
    }

    public function riwayatReturSupplier(Request $request)
    {
        $query = StokKeluar::with(['supplier', 'user', 'details.barang'])
            ->where('jenis_keluar', 'retur');

        if ($request->filled('search')) {
            $query->where('no_transaksi', 'like', '%' . trim($request->search) . '%');
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('dari')) {
            $query->whereDate('tanggal_keluar', '>=', $request->dari);
        }
        if ($request->filled('sampai')) {
            $query->whereDate('tanggal_keluar', '<=', $request->sampai);
        }

        // Ringkasan dari seluruh hasil filter (tanpa pagination)
        $semua = (clone $query)->get();
        $totalTransaksi = $semua->count();
        $totalQty       = $semua->sum(fn ($r) => $r->details->sum('jumlah'));
        $totalNilai     = $semua->sum(fn ($r) => $r->total);

        $returSuppliers = $query->latest()->paginate(15)->withQueryString();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('retur.supplier_riwayat', compact(
            'returSuppliers', 'suppliers', 'totalTransaksi', 'totalQty', 'totalNilai'
        ));
    }

    public function showReturSupplier(StokKeluar $stokKeluar)
    {
        abort_unless($stokKeluar->jenis_keluar === 'retur', 404);

        $stokKeluar->load(['supplier', 'user', 'details.barang']);

        return view('retur.supplier_show', compact('stokKeluar'));
    }

    public function destroyReturSupplier(StokKeluar $stokKeluar)
    {
        abort_unless($stokKeluar->jenis_keluar === 'retur', 404);

        DB::transaction(function () use ($stokKeluar) {
            // Temukan nota stok masuk asal retur (dari keterangan)
            $stokMasuk = null;
            if (preg_match('/Retur dari nota: (.+?) - Alasan:/', (string) $stokKeluar->keterangan, $m)) {
                $stokMasuk = StokMasuk::where('no_transaksi', trim($m[1]))->first();
            }

            foreach ($stokKeluar->details as $detail) {
                // Kembalikan stok barang
                Barang::where('id', $detail->barang_id)
                    ->increment('stok_saat_ini', $detail->jumlah);

                // Catat pembatalan ke log mutasi stok
                MutasiStok::catat($detail->barang_id, 'masuk', $detail->jumlah, 'Pembatalan retur ke supplier ' . $stokKeluar->no_transaksi);

                // Pulihkan sisa retur pada detail nota stok masuk
                if ($stokMasuk) {
                    $smDetail = StokMasukDetail::where('stok_masuk_id', $stokMasuk->id)
                        ->where('barang_id', $detail->barang_id)
                        ->first();
                    if ($smDetail) {
                        $smDetail->update([
                            'jumlah_diretur' => max(0, (int) $smDetail->jumlah_diretur - (int) $detail->jumlah),
                        ]);
                    }
                }
            }
            $stokKeluar->details()->delete();
            $stokKeluar->delete();
        });

        return redirect()->route('retur.supplier-riwayat')
            ->with('success', 'Retur ke supplier dibatalkan. Stok telah dikembalikan.');
    }
}
