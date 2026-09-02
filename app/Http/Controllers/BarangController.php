<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\BarangSupplier;
use App\Models\Supplier;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    public function index(Request $request)
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

        $barangs   = $query->orderBy('nama_barang')->paginate(15)->withQueryString();
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();

        return view('barang.index', compact('barangs', 'kategoris', 'suppliers'));
    }

    public function create()
    {
        $kategoris  = Kategori::orderBy('nama_kategori')->get();
        $suppliers  = Supplier::orderBy('nama_supplier')->get();
        $kodeBarang = ''; // Will be generated after kategori & nama chosen
        return view('barang.create', compact('kategoris', 'suppliers', 'kodeBarang'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_barang'   => 'required|unique:barangs,kode_barang',
            'nama_barang'   => 'required|max:255',
            'kategori_id'   => 'required|exists:kategoris,id',
            'satuan'        => 'required|max:50',
            'harga_beli'    => 'required|numeric|min:0',
            'harga_jual'    => 'required|numeric|min:0',
            'stok_saat_ini' => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'stok_maksimum' => 'required|integer|min:0|gte:stok_minimum',
            'lokasi_rak'    => 'nullable|max:100',
            'deskripsi'     => 'nullable',
            'preferred_supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $data = $request->all();

        // Jika kode belum di-generate (placeholder ???), buat otomatis dari nama barang
        if (isset($data['kode_barang']) && str_contains($data['kode_barang'], '???')) {
            $data['kode_barang'] = Barang::generateKode($request->nama_barang);
        }

        $barang = Barang::create($data);

        // Simpan relasi supplier jika ada
        if ($request->preferred_supplier_id) {
            BarangSupplier::create([
                'barang_id' => $barang->id,
                'supplier_id' => $request->preferred_supplier_id,
                'is_preferred' => true,
            ]);
        }

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil ditambahkan.');
    }

    public function show(Barang $barang)
    {
        $barang->load('kategori');
        return view('barang.show', compact('barang'));
    }

    public function edit(Barang $barang)
    {
        $kategoris = Kategori::orderBy('nama_kategori')->get();
        $suppliers = Supplier::orderBy('nama_supplier')->get();
        return view('barang.edit', compact('barang', 'kategoris', 'suppliers'));
    }

    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'kode_barang'   => 'required|unique:barangs,kode_barang,' . $barang->id,
            'nama_barang'   => 'required|max:255',
            'kategori_id'   => 'required|exists:kategoris,id',
            'satuan'        => 'required|max:50',
            'harga_beli'    => 'required|numeric|min:0',
            'harga_jual'    => 'required|numeric|min:0',
            'stok_saat_ini' => 'required|integer|min:0',
            'stok_minimum'  => 'required|integer|min:0',
            'stok_maksimum' => 'required|integer|min:0|gte:stok_minimum',
            'lokasi_rak'    => 'nullable|max:100',
            'deskripsi'     => 'nullable',
            'preferred_supplier_id' => 'nullable|exists:suppliers,id',
        ]);

        $barang->update($request->all());

        // Update pivot preferred supplier
        if ($request->preferred_supplier_id) {
            BarangSupplier::updateOrCreate(
                ['barang_id' => $barang->id, 'supplier_id' => $request->preferred_supplier_id],
                ['is_preferred' => true]
            );

            // Set supplier lain jadi non-preferred
            BarangSupplier::where('barang_id', $barang->id)
                ->where('supplier_id', '!=', $request->preferred_supplier_id)
                ->update(['is_preferred' => false]);
        }

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil diperbarui.');
    }

    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('barang.index')
            ->with('success', 'Barang berhasil dihapus.');
    }
}