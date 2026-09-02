<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Kategori;
use App\Models\Supplier;
use App\Models\Transaksi;

class LandingController extends Controller
{
    /**
     * Halaman depan publik. Jika sudah login, langsung tampilkan dashboard.
     */
    public function index()
    {
        if (auth()->check()) {
            return app(DashboardController::class)->index();
        }

        // Statistik nyata untuk ditampilkan di landing page
        $totalBarang    = Barang::where('is_active', true)->count();
        $totalKategori  = Kategori::count();
        $totalSupplier  = Supplier::where('is_active', true)->count();
        $totalTransaksi = Transaksi::count();
        $stokKritis     = Barang::where('is_active', true)
            ->whereRaw('stok_saat_ini <= stok_minimum')->count();

        return view('landing', compact(
            'totalBarang', 'totalKategori', 'totalSupplier',
            'totalTransaksi', 'stokKritis'
        ));
    }
}
