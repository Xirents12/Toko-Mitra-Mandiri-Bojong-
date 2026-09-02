<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        // Pencarian berdasarkan kode / nama / telepon / email
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_supplier', 'like', "%{$search}%")
                    ->orWhere('nama_supplier', 'like', "%{$search}%")
                    ->orWhere('telepon', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif' ? 1 : 0);
        }

        $suppliers = $query->orderBy('nama_supplier')->paginate(15)->withQueryString();

        $totalSupplier    = Supplier::count();
        $totalAktif       = Supplier::where('is_active', true)->count();
        $totalNonaktif    = Supplier::where('is_active', false)->count();

        return view('supplier.index', compact('suppliers', 'totalSupplier', 'totalAktif', 'totalNonaktif'));
    }

    public function create()
    {
        return view('supplier.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_supplier' => 'required|max:255',
            'telepon'       => 'nullable|max:30',
            'email'         => 'nullable|email|max:255',
            'alamat'        => 'nullable|max:255',
            'keterangan'    => 'nullable|max:500',
            'is_active'     => 'nullable|boolean',
        ]);

        Supplier::create([
            'kode_supplier' => Supplier::generateKode($request->nama_supplier),
            'nama_supplier' => $request->nama_supplier,
            'telepon'       => $request->telepon,
            'email'         => $request->email,
            'alamat'        => $request->alamat,
            'keterangan'    => $request->keterangan,
            'is_active'     => $request->is_active ?? 1,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier)
    {
        return view('supplier.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'nama_supplier' => 'required|max:255',
            'telepon'       => 'nullable|max:30',
            'email'         => 'nullable|email|max:255',
            'alamat'        => 'nullable|max:255',
            'keterangan'    => 'nullable|max:500',
            'is_active'     => 'nullable|boolean',
        ]);

        $supplier->update([
            'nama_supplier' => $request->nama_supplier,
            'telepon'       => $request->telepon,
            'email'         => $request->email,
            'alamat'        => $request->alamat,
            'keterangan'    => $request->keterangan,
            'is_active'     => $request->is_active ?? $supplier->is_active,
        ]);

        return redirect()->route('supplier.index')->with('success', 'Data supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        // Cegah penghapusan jika supplier masih dipakai
        $dipakaiPO        = PurchaseOrder::where('supplier_id', $supplier->id)->exists();
        $dipakaiStokMasuk = $supplier->stokMasuks()->exists();
        $dipakaiBarang    = Barang::where('preferred_supplier_id', $supplier->id)->exists();

        if ($dipakaiPO || $dipakaiStokMasuk || $dipakaiBarang) {
            return redirect()->route('supplier.index')->with('error', 'Supplier tidak bisa dihapus karena masih digunakan pada PO, stok masuk, atau barang.');
        }

        $supplier->delete();

        return redirect()->route('supplier.index')->with('success', 'Supplier berhasil dihapus.');
    }
}
