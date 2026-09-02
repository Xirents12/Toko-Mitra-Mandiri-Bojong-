<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\MutasiStok;
use App\Models\StokMasuk;
use App\Models\Kategori;
use App\Models\Transaksi;
use App\Models\Piutang;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Ringkasan stok
        $totalBarang       = Barang::where('is_active', true)->count();
        $stokKritis        = Barang::where('is_active', true)->whereRaw('stok_saat_ini <= stok_minimum')->count();
        $stokOverstock     = Barang::where('is_active', true)->whereRaw('stok_saat_ini >= stok_maksimum')->where('stok_maksimum', '>', 0)->count();
        $totalKategori     = Kategori::count();

        // Transaksi bulan ini
        $masukBulanIni  = StokMasuk::whereMonth('tanggal_masuk', now()->month)->whereYear('tanggal_masuk', now()->year)->count();

        // Daftar stok kritis (untuk alert)
        $barangKritis = Barang::with('kategori', 'preferredSupplier')
            ->where('is_active', true)
            ->whereRaw('stok_saat_ini <= stok_minimum')
            ->orderBy('stok_saat_ini')
            ->limit(10)
            ->get();

        // Transaksi terbaru
        $transaksiMasukTerbaru = StokMasuk::with(['supplier','user'])
            ->latest()->limit(5)->get();

        // Grafik mutasi stok 7 hari terakhir (dari log mutasi otomatis)
        $grafikMasuk = MutasiStok::selectRaw('DATE(created_at) as tgl, COUNT(*) as total')
            ->where('tipe', 'masuk')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('tgl')->orderBy('tgl')->pluck('total', 'tgl');

        $grafikKeluar = MutasiStok::selectRaw('DATE(created_at) as tgl, COUNT(*) as total')
            ->where('tipe', 'keluar')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('tgl')->orderBy('tgl')->pluck('total', 'tgl');

        // ── Statistik tambahan ──
        // Penjualan hari ini
        $penjualanHariIni = Transaksi::whereDate('created_at', today())
            ->where('metode_bayar', 'tunai')
            ->sum('total_harga');

        // Piutang hari ini
        $piutangHariIni = Transaksi::whereDate('created_at', today())
            ->where('metode_bayar', 'kredit')
            ->sum('total_harga');

        // Total piutang aktif
        $totalPiutangAktif = Piutang::where('status', 'aktif')->sum('sisa_piutang');

        // PO belum selesai (pending aksi) — kasir tidak punya akses PO
        $poPending = $user->isKasir()
            ? 0
            : PurchaseOrder::whereNotIn('status', ['selesai', 'ditolak', 'dibatalkan'])->count();

        // Transaksi POS hari ini
        $transaksiPosHariIni = Transaksi::whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Penjualan per hari (7 hari)
        $grafikPenjualan = Transaksi::selectRaw('DATE(created_at) as tgl, SUM(total_harga) as total')
            ->where('created_at', '>=', now()->subDays(6))
            ->groupBy('tgl')->orderBy('tgl')->pluck('total', 'tgl');

        // ── Siapkan sumbu grafik 7 hari terakhir (hari tanpa data = 0) ──
        $hariGrafik = collect();
        for ($i = 6; $i >= 0; $i--) {
            $hariGrafik->put(now()->subDays($i)->format('Y-m-d'), 0);
        }
        $nilaiPenjualan = $hariGrafik->merge($grafikPenjualan)->sortKeys();
        $nilaiMasuk     = $hariGrafik->merge($grafikMasuk)->sortKeys();
        $nilaiKeluar    = $hariGrafik->merge($grafikKeluar)->sortKeys();

        $labelsGrafik    = $nilaiPenjualan->keys()->map(fn ($t) => Carbon::parse($t)->translatedFormat('D'))->values();
        $dataPenjualan   = $nilaiPenjualan->values();
        $dataMasuk       = $nilaiMasuk->values();
        $dataKeluar      = $nilaiKeluar->values();

        return view('dashboardindex', compact(
            'totalBarang','stokKritis','stokOverstock','totalKategori',
            'masukBulanIni',
            'barangKritis','transaksiMasukTerbaru',
            'grafikMasuk','grafikKeluar',
            'penjualanHariIni','piutangHariIni','totalPiutangAktif','poPending',
            'transaksiPosHariIni',
            'labelsGrafik','dataPenjualan','dataMasuk','dataKeluar'
        ));
    }
}
