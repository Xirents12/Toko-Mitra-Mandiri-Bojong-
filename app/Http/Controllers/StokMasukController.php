<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\MutasiStok;
use App\Models\StokMasuk;
use App\Models\StokMasukDetail;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StokMasukController extends Controller
{
    public function index(Request $request)
    {
        $query = StokMasuk::with(['supplier', 'user', 'details.barang']);

        if ($request->search) {
            $query->where('no_transaksi', 'like', '%' . $request->search . '%');
        }

        if ($request->tanggal_awal && $request->tanggal_akhir) {
            $query->whereBetween('tanggal_masuk', [
                $request->tanggal_awal,
                $request->tanggal_akhir
            ]);
        }

        $stokMasuks = $query->orderByDesc('tanggal_masuk')->paginate(15)->withQueryString();

        return view('stok_masuk.index', compact('stokMasuks'));
    }

    /**
     * Riwayat barang masuk / stok masuk (per item) dengan filter & ringkasan.
     */
    public function riwayat(Request $request)
    {
        $query = StokMasukDetail::query()
            ->join('stok_masuks', 'stok_masuk_details.stok_masuk_id', '=', 'stok_masuks.id')
            ->with(['stokMasuk.supplier', 'stokMasuk.user', 'barang'])
            ->select('stok_masuk_details.*', 'stok_masuks.tanggal_masuk');

        if ($request->filled('search')) {
            $search = '%' . trim($request->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('stok_masuks.no_transaksi', 'like', $search)
                    ->orWhereHas('barang', function ($b) use ($search) {
                        $b->where('nama_barang', 'like', $search)
                            ->orWhere('kode_barang', 'like', $search);
                    });
            });
        }

        if ($request->filled('supplier_id')) {
            $query->where('stok_masuks.supplier_id', $request->supplier_id);
        }

        if ($request->filled('dari')) {
            $query->whereDate('stok_masuks.tanggal_masuk', '>=', $request->dari);
        }

        if ($request->filled('sampai')) {
            $query->whereDate('stok_masuks.tanggal_masuk', '<=', $request->sampai);
        }

        // Ringkasan dari seluruh hasil filter (tanpa pagination)
        $semua = (clone $query)->get();
        $totalTransaksi = $semua->pluck('stok_masuk_id')->unique()->count();
        $totalQty       = $semua->sum('jumlah');
        $totalNilai     = $semua->sum(fn ($d) => $d->jumlah * $d->harga_beli);
        $totalJenis     = $semua->pluck('barang_id')->unique()->count();

        $riwayats = $query
            ->orderByDesc('stok_masuks.tanggal_masuk')
            ->orderByDesc('stok_masuk_details.id')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('stok_masuk.riwayat', compact(
            'riwayats', 'suppliers', 'totalTransaksi', 'totalQty', 'totalNilai', 'totalJenis'
        ));
    }

    public function create()
    {
        $barangs   = Barang::where('is_active', true)->orderBy('nama_barang')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        $noTransaksi = StokMasuk::generateNoTransaksi();

        return view('stok_masuk.create', compact('barangs', 'suppliers', 'noTransaksi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_masuk'         => 'required|date',
            'supplier_id'           => 'nullable|exists:suppliers,id',
            'no_nota_supplier'      => 'nullable|string|max:100',
            'keterangan'            => 'nullable|string',
            'details'               => 'required|array|min:1',
            'details.*.barang_id'   => 'required|exists:barangs,id',
            'details.*.jumlah'      => 'required|integer|min:1',
            'details.*.harga_beli'  => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $noTransaksi = StokMasuk::generateNoTransaksi();

            $stokMasuk = StokMasuk::create([
                'no_transaksi'     => $noTransaksi,
                'tanggal_masuk'    => $request->tanggal_masuk,
                'supplier_id'      => $request->supplier_id,
                'no_nota_supplier' => $request->no_nota_supplier,
                'keterangan'       => $request->keterangan,
                'user_id'          => Auth::id(),
            ]);

            foreach ($request->details as $detail) {
                StokMasukDetail::create([
                    'stok_masuk_id' => $stokMasuk->id,
                    'barang_id'     => $detail['barang_id'],
                    'jumlah'        => $detail['jumlah'],
                    'harga_beli'    => $detail['harga_beli'],
                    'keterangan'    => $detail['keterangan'] ?? null,
                ]);

                // Update stok barang
                Barang::where('id', $detail['barang_id'])
                    ->increment('stok_saat_ini', $detail['jumlah']);

                // Catat ke log mutasi stok
                MutasiStok::catat($detail['barang_id'], 'masuk', $detail['jumlah'], 'Stok masuk ' . $noTransaksi);
            }
        });

        return redirect()->route('stok-masuk.index')
            ->with('success', 'Stok masuk berhasil dicatat.');
    }

    public function show(StokMasuk $stokMasuk)
    {
        $stokMasuk->load(['supplier', 'user', 'details.barang.kategori']);
        return view('stok_masuk.show', compact('stokMasuk'));
    }

    public function destroy(StokMasuk $stokMasuk)
    {
        DB::transaction(function () use ($stokMasuk) {
            // Kembalikan stok barang
            foreach ($stokMasuk->details as $detail) {
                Barang::where('id', $detail->barang_id)
                    ->decrement('stok_saat_ini', $detail->jumlah);

                // Catat pembatalan ke log mutasi stok
                MutasiStok::catat($detail->barang_id, 'keluar', $detail->jumlah, 'Pembatalan stok masuk ' . $stokMasuk->no_transaksi);
            }
            $stokMasuk->details()->delete();
            $stokMasuk->delete();
        });

        return redirect()->route('stok-masuk.index')
            ->with('success', 'Data stok masuk berhasil dihapus.');
    }
}