@extends('layouts.app')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@php
    $user = auth()->user();
    $cards = [];

    // Penjualan & per nota (Admin)
    if ($user->isAdmin()) {
        $cards[] = ['route' => 'laporan.penjualan',  'icon' => 'bi-receipt',              'color' => 'success',  'title' => 'Penjualan',            'desc' => 'Rekap transaksi penjualan kasir'];
    }

    // Laporan stok (semua role)
    if ($user->isAdmin() || $user->isGudang() || $user->isKasir()) {
        $cards[] = ['route' => 'laporan.stok',       'icon' => 'bi-box-seam',             'color' => 'primary',  'title' => 'Stok Barang',          'desc' => 'Lihat kondisi stok semua barang'];
    }

    // Stok kritis, overstok, & mutasi (Admin, Gudang)
    if ($user->isAdmin() || $user->isGudang()) {
        $cards[] = ['route' => 'laporan.stok-kritis','icon' => 'bi-exclamation-triangle-fill', 'color' => 'danger', 'title' => 'Stok Kritis',       'desc' => 'Barang hampir habis / di bawah minimum'];
        $cards[] = ['route' => 'laporan.overstok',   'icon' => 'bi-exclamation-diamond',  'color' => 'warning',  'title' => 'Stok Overstok',         'desc' => 'Barang dengan stok melebihi batas maksimum'];
        $cards[] = ['route' => 'laporan.mutasi',     'icon' => 'bi-arrow-left-right',     'color' => 'info',     'title' => 'Mutasi Stok',           'desc' => 'Riwayat stok masuk & keluar'];
        $cards[] = ['route' => 'laporan.mutasi-detail', 'icon' => 'bi-clock-history',     'color' => 'secondary','title' => 'Riwayat Mutasi Stok',  'desc' => 'Log otomatis mutasi stok per barang'];
    }
@endphp

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Pusat Laporan</h5>
    <small class="text-muted">Menampilkan laporan sesuai peran Anda</small>
</div>

<div class="row g-3">
    @forelse($cards as $card)
    <div class="col-md-3">
        <a href="{{ route($card['route']) }}"
           class="card border-0 shadow-sm text-decoration-none text-dark h-100">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-{{ $card['color'] }} bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-3 p-3">
                    <i class="bi {{ $card['icon'] }} fs-3 text-{{ $card['color'] }}"></i>
                </div>
                <div class="fw-semibold">{{ $card['title'] }}</div>
                <small class="text-muted">{{ $card['desc'] }}</small>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 shadow-sm text-center py-5 text-muted">
            <i class="bi bi-inbox fs-3 d-block mb-2"></i>
            Belum ada laporan yang tersedia untuk peran Anda.
        </div>
    </div>
    @endforelse
</div>
@endsection
