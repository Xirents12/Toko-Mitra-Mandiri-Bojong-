<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\StokMasukController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PiutangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// ───── Landing Page (publik) ─────
// Tamu melihat landing page; yang sudah login langsung diarahkan ke dashboard oleh controller.
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ───── Auth ─────
Route::get('/login',  [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/daftar',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/daftar', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ───── Protected Routes ─────
Route::middleware(['auth'])->group(function () {

    // Dashboard - semua role (URL '/' ditangani LandingController: tamu → landing, login → dashboard)

    // Profil & ubah password - semua role
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profil/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // ── SUPPLIER: manajemen data supplier (admin & gudang) ──
    Route::middleware(['role:admin,gudang'])->group(function () {
        Route::get('/supplier',                [SupplierController::class, 'index'])->name('supplier.index');
        Route::get('/supplier/create',         [SupplierController::class, 'create'])->name('supplier.create');
        Route::post('/supplier',               [SupplierController::class, 'store'])->name('supplier.store');
        Route::get('/supplier/{supplier}/edit',[SupplierController::class, 'edit'])->name('supplier.edit');
        Route::put('/supplier/{supplier}',     [SupplierController::class, 'update'])->name('supplier.update');
        Route::delete('/supplier/{supplier}',  [SupplierController::class, 'destroy'])->name('supplier.destroy');
    });

    // ── Manajemen pengguna DIMATIKAN (routes dihapus; controller & view tetap ada) ──

    // ── GUDANG ONLY: operasional barang & stok (register FIRST for route ordering) ──
    Route::middleware(['role:gudang'])->group(function () {
        // Barang
        Route::get('/barang/create',        [BarangController::class, 'create'])->name('barang.create');
        Route::post('/barang',              [BarangController::class, 'store'])->name('barang.store');
        Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
        Route::put('/barang/{barang}',      [BarangController::class, 'update'])->name('barang.update');
        Route::delete('/barang/{barang}',   [BarangController::class, 'destroy'])->name('barang.destroy');

        // Stok Masuk - specific routes BEFORE parameterized
        Route::get('/stok-masuk/create',        [StokMasukController::class, 'create'])->name('stok-masuk.create');
        Route::post('/stok-masuk',              [StokMasukController::class, 'store'])->name('stok-masuk.store');
        Route::delete('/stok-masuk/{stokMasuk}',[StokMasukController::class, 'destroy'])->name('stok-masuk.destroy');

        // Fitur Stok Keluar dimatikan (penjualan ditangani Kasir/POS)
    });

    // ── PEMBELIAN / PURCHASE ORDER (RBAC) ──
    // Daftar, riwayat, laporan, penerimaan (semua role bisa lihat; aksi dibatasi peran)
    Route::middleware(['role:admin,gudang'])->group(function () {
        Route::get('/purchase-order',               [PurchaseOrderController::class, 'index'])->name('purchase-order.index');
        Route::get('/purchase-order/riwayat',       [PurchaseOrderController::class, 'riwayat'])->name('purchase-order.riwayat');
        Route::get('/purchase-order/laporan',       [PurchaseOrderController::class, 'laporan'])->name('purchase-order.laporan');
        Route::get('/purchase-order/penerimaan',    [PurchaseOrderController::class, 'penerimaan'])->name('purchase-order.penerimaan');
        Route::get('/purchase-order/{purchaseOrder}/cetak', [PurchaseOrderController::class, 'cetak'])->name('purchase-order.cetak');
    });

    // Buat / edit / ajukan / batalkan / hapus — hanya Gudang (Admin hanya menyetujui)
    Route::middleware(['role:gudang'])->group(function () {
        // Form "Buat PO" — pemesanan barang kritis (klik barang kritis → masuk pesanan → kirim ke admin)
        Route::get('/purchase-order/create',         [PurchaseOrderController::class, 'create'])->name('purchase-order.create');
        Route::post('/purchase-order',               [PurchaseOrderController::class, 'store'])->name('purchase-order.store');
        // PO otomatis dari stok kritis (sekali klik, langsung ke persetujuan admin)
        Route::post('/purchase-order/auto-stok-kritis', [PurchaseOrderController::class, 'buatOtomatis'])->name('purchase-order.auto-kritis');
        Route::get('/purchase-order/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-order.edit');
        Route::put('/purchase-order/{purchaseOrder}',[PurchaseOrderController::class, 'update'])->name('purchase-order.update');
        Route::post('/purchase-order/{purchaseOrder}/ajukan', [PurchaseOrderController::class, 'ajukan'])->name('purchase-order.ajukan');
        Route::post('/purchase-order/{purchaseOrder}/batalkan', [PurchaseOrderController::class, 'batalkan'])->name('purchase-order.batalkan');
        Route::delete('/purchase-order/{purchaseOrder}',[PurchaseOrderController::class, 'destroy'])->name('purchase-order.destroy');
    });

    // Pembuatan PO ke supplier & persetujuan — hanya Pemilik (Admin)
    Route::middleware(['role:admin'])->group(function () {
        // DIMATIKAN: "Buat PO ke Supplier" untuk admin. Admin cukup Setujui / Tolak permintaan gudang.
        // Route::post('/purchase-order/{purchaseOrder}/buat-po', [PurchaseOrderController::class, 'buatPo'])->name('purchase-order.buat-po');
        Route::post('/purchase-order/{purchaseOrder}/setujui', [PurchaseOrderController::class, 'setujui'])->name('purchase-order.setujui');
        Route::post('/purchase-order/{purchaseOrder}/tolak',   [PurchaseOrderController::class, 'tolak'])->name('purchase-order.tolak');
    });

    // Tandai dikirim supplier — hanya Gudang
    Route::middleware(['role:gudang'])->group(function () {
        Route::post('/purchase-order/{purchaseOrder}/kirim', [PurchaseOrderController::class, 'kirim'])->name('purchase-order.kirim');
    });

    // Penerimaan barang — hanya Bagian Gudang
    Route::middleware(['role:gudang'])->group(function () {
        Route::get('/purchase-order/{purchaseOrder}/terima',  [PurchaseOrderController::class, 'terima'])->name('purchase-order.terima');
        Route::post('/purchase-order/{purchaseOrder}/terima', [PurchaseOrderController::class, 'terimaSimpan'])->name('purchase-order.terima-simpan');
    });

    // ── SEMUA ROLE: lihat stok & transaksi (read only untuk monitoring) ──
    Route::middleware(['role:admin,gudang'])->group(function () {
        Route::get('/stok-masuk',          [StokMasukController::class, 'index'])->name('stok-masuk.index');
        Route::get('/stok-masuk/riwayat',   [StokMasukController::class, 'riwayat'])->name('stok-masuk.riwayat');
        Route::get('/stok-masuk/{stokMasuk}',[StokMasukController::class, 'show'])->name('stok-masuk.show');
    });

    // Detail PO — didaftarkan TERAKHIR agar tidak menabrak route spesifik di atas
    Route::middleware(['role:admin,gudang'])->group(function () {
        Route::get('/purchase-order/{purchaseOrder}',[PurchaseOrderController::class, 'show'])->name('purchase-order.show');
    });

    // ── SEMUA ROLE: lihat barang (read only) ──
    Route::middleware(['role:admin,gudang,kasir'])->group(function () {
        Route::get('/barang',         [BarangController::class, 'index'])->name('barang.index');
        Route::get('/barang/{barang}',[BarangController::class, 'show'])->name('barang.show');
    });

    // ── KASIR ONLY: POS, retur, bayar piutang ──
    Route::middleware(['role:kasir'])->group(function () {
        Route::get('/pos',                    [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos',                   [PosController::class, 'store'])->name('pos.store');
        Route::get('/pos/struk/{transaksi}',  [PosController::class, 'struk'])->name('pos.struk');
        Route::get('/pos/riwayat',            [PosController::class, 'riwayat'])->name('pos.riwayat');



        // Piutang - bayar cicilan (kasir)
        Route::get('/piutang/{piutang}/bayar',  [PiutangController::class, 'formBayar'])->name('piutang.bayar');
        Route::post('/piutang/{piutang}/bayar', [PiutangController::class, 'bayarCicilan'])->name('piutang.bayar-cicilan');
    });

    // ── KASIR & GUDANG: retur barang (dari pelanggan/konsumen) ──
    Route::middleware(['role:kasir,gudang'])->group(function () {
        Route::get('/retur',      [ReturController::class, 'index'])->name('retur.index');
        Route::get('/retur/cari', [ReturController::class, 'cariTransaksi'])->name('retur.cari');
    });

    // ── GUDANG ONLY: retur ke supplier (berbasis nota stok masuk) ──
    // DIDAFTARKAN SEBELUM /retur/{transaksi} agar '/retur/supplier' tidak tertangkap parameter
    // Route literal (cari/riwayat) DIDAFTARKAN sebelum /retur/supplier/{stokKeluar}
    Route::middleware(['role:gudang'])->group(function () {
        Route::get('/retur/supplier',                  [ReturController::class, 'returSupplier'])->name('retur.supplier');
        Route::get('/retur/supplier/cari',             [ReturController::class, 'cariStokMasuk'])->name('retur.supplier-cari');
        Route::get('/retur/supplier/riwayat',          [ReturController::class, 'riwayatReturSupplier'])->name('retur.supplier-riwayat');
        Route::get('/retur/supplier/{stokMasuk}/form', [ReturController::class, 'formReturSupplier'])->name('retur.supplier-form');
        Route::post('/retur/supplier/{stokMasuk}',     [ReturController::class, 'prosesReturSupplier'])->name('retur.supplier-proses');
        Route::get('/retur/supplier/{stokKeluar}',     [ReturController::class, 'showReturSupplier'])->name('retur.supplier-show');
        Route::delete('/retur/supplier/{stokKeluar}',  [ReturController::class, 'destroyReturSupplier'])->name('retur.supplier-destroy');
    });

    // ── KASIR & GUDANG: proses retur dari pelanggan (parameterized) ──
    Route::middleware(['role:kasir,gudang'])->group(function () {
        Route::get('/retur/{transaksi}', [ReturController::class, 'formRetur'])->name('retur.form');
        Route::post('/retur/{transaksi}',[ReturController::class, 'prosesRetur'])->name('retur.proses');
    });

    // ── SEMUA ROLE: piutang & laporan (read only untuk monitoring) ──
    Route::middleware(['role:admin,gudang,kasir'])->group(function () {
        Route::get('/piutang',         [PiutangController::class, 'index'])->name('piutang.index');
        Route::get('/piutang/{piutang}',[PiutangController::class, 'show'])->name('piutang.show');
        Route::get('/laporan/piutang', [PiutangController::class, 'laporan'])->name('piutang.laporan');
    });

    // ── Laporan - semua role ──
    Route::prefix('laporan')->name('laporan.')->middleware(['role:admin,gudang,kasir'])->group(function () {
        Route::get('/',            [LaporanController::class, 'index'])->name('index');
        Route::get('/stok',        [LaporanController::class, 'stok'])->name('stok');
        Route::get('/stok-kritis', [LaporanController::class, 'stokKritis'])->name('stok-kritis');
        Route::get('/mutasi',      [LaporanController::class, 'mutasi'])->name('mutasi');
        Route::get('/mutasi-detail', [LaporanController::class, 'mutasiDetail'])->name('mutasi-detail');
        Route::get('/penjualan',   [LaporanController::class, 'penjualan'])->name('penjualan');
        Route::get('/penjualan/{transaksi}/cetak', [LaporanController::class, 'cetakNota'])->name('cetak-nota');
        Route::get('/per-nota',     [LaporanController::class, 'perNota'])->name('per-nota');
        Route::get('/overstok',    [LaporanController::class, 'overstok'])->name('overstok');
    });
});

