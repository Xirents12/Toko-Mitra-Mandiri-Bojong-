<?php

namespace App\Http\Controllers;

use App\Models\StokKeluar;
use App\Models\StokKeluarDetail;
use App\Models\Barang;
use App\Models\MutasiStok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StokKeluarController extends Controller
{
    public function index(Request $request)
    {
        $query = StokKeluar::with('user');

        if ($request->search) {
            $query->where('no_transaksi', 'like', "%{$request->search}%")
                  ->orWhere('nama_pelanggan', 'like', "%{$request->search}%");
        }
        if ($request->tanggal_dari) {
            $query->where('tanggal_keluar', '>=', $request->tanggal_dari);
        }
        if ($request->tanggal_sampai) {
            $query->where('tanggal_keluar', '<=', $request->tanggal_sampai);
        }

        $stokKeluars = $query->with(['user', 'details.barang'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('stok_keluar.index', compact('stokKeluars'));
    }

    public function create()
    {
        $barangs     = Barang::where('is_active', true)->where('stok_saat_ini', '>', 0)->orderBy('nama_barang')->get();
        $noTransaksi = StokKeluar::generateNoTransaksi();
        return view('stok_keluar.create', compact('barangs', 'noTransaksi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal_keluar'  => 'required|date',
            'nama_pelanggan'  => 'nullable|max:255',
            'jenis_keluar'    => 'required|in:penjualan,pemakaian,retur,lainnya',
            'keterangan'      => 'nullable',
            'barang_id'       => 'required|array|min:1',
            'barang_id.*'     => 'required|exists:barangs,id',
            'jumlah.*'        => 'required|integer|min:1',
            'harga_jual.*'    => 'required|numeric|min:0',
        ]);

        // Validasi stok mencukupi
        foreach ($request->barang_id as $index => $barangId) {
            $barang = Barang::find($barangId);
            $jumlah = $request->jumlah[$index];
            if ($barang->stok_saat_ini < $jumlah) {
                return back()->withInput()->withErrors([
                    "jumlah.{$index}" => "Stok {$barang->nama_barang} tidak mencukupi. Stok tersedia: {$barang->stok_saat_ini} {$barang->satuan}."
                ]);
            }
        }

        DB::transaction(function () use ($request) {
            $stokKeluar = StokKeluar::create([
                'no_transaksi'   => StokKeluar::generateNoTransaksi(),
                'tanggal_keluar' => $request->tanggal_keluar,
                'nama_pelanggan' => $request->nama_pelanggan,
                'jenis_keluar'   => $request->jenis_keluar,
                'keterangan'     => $request->keterangan,
                'user_id'        => Auth::id(),
            ]);

            foreach ($request->barang_id as $index => $barangId) {
                $jumlah = $request->jumlah[$index];

                StokKeluarDetail::create([
                    'stok_keluar_id' => $stokKeluar->id,
                    'barang_id'      => $barangId,
                    'jumlah'         => $jumlah,
                    'harga_jual'     => $request->harga_jual[$index],
                    'keterangan'     => $request->keterangan_detail[$index] ?? null,
                ]);

                // Kurangi stok barang secara real-time
                Barang::where('id', $barangId)->decrement('stok_saat_ini', $jumlah);

                // Catat ke log mutasi stok
                MutasiStok::catat($barangId, 'keluar', $jumlah, 'Stok keluar ' . $stokKeluar->no_transaksi . ' (' . $stokKeluar->jenis_keluar . ')');
            }
        });

        return redirect()->route('stok-keluar.index')->with('success', 'Stok keluar berhasil dicatat.');
    }

    public function show(StokKeluar $stokKeluar)
    {
        $stokKeluar->load(['user', 'details.barang']);
        return view('stok_keluar.show', compact('stokKeluar'));
    }

    public function destroy(StokKeluar $stokKeluar)
    {
        DB::transaction(function () use ($stokKeluar) {
            foreach ($stokKeluar->details as $detail) {
                Barang::where('id', $detail->barang_id)->increment('stok_saat_ini', $detail->jumlah);

                // Catat pembatalan ke log mutasi stok
                MutasiStok::catat($detail->barang_id, 'masuk', $detail->jumlah, 'Pembatalan stok keluar ' . $stokKeluar->no_transaksi);
            }
            $stokKeluar->delete();
        });

        return redirect()->route('stok-keluar.index')->with('success', 'Transaksi stok keluar berhasil dihapus.');
    }
}
