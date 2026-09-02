<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem Stok Gudang') — Toko Mitra Mandiri Bojong</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --accent: #3b82f6; --accent-2: #6366f1; }
        body.role-admin  { --accent: #6366f1; --accent-2: #8b5cf6; } /* Ungu - Owner */
        body.role-gudang { --accent: #10b981; --accent-2: #34d399; } /* Hijau - Bagian Gudang */
        body.role-kasir  { --accent: #f59e0b; --accent-2: #fbbf24; } /* Oranye - Kasir */

        * { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; }

        /* ═══════════ SIDEBAR ═══════════ */
        #sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(180deg, #0b1220 0%, #111c33 55%, #17233d 100%);
            position: fixed;
            top: 0;
            left: 0;
            transition: transform .3s ease;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .sidebar-body { flex: 1; overflow-y: auto; padding: .4rem .9rem 1rem; }
        .sidebar-body::-webkit-scrollbar { width: 5px; }
        .sidebar-body::-webkit-scrollbar-thumb { background: rgba(255,255,255,.14); border-radius: 3px; }

        .sidebar-brand { padding: 1.25rem .9rem 1rem; border-bottom: 1px solid rgba(255,255,255,.07); }
        .brand-logo {
            width: 42px; height: 42px; border-radius: .65rem;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 6px 16px rgba(0,0,0,.35);
            font-size: 1.2rem; color: #fff; flex-shrink: 0;
        }
        .sidebar-brand h6 { color: #fff; font-weight: 800; font-size: .95rem; margin: 0; }
        .sidebar-brand small { color: #94a3b8; font-size: .72rem; }
        .brand-chip {
            display: inline-flex; align-items: center; gap: .35rem;
            margin-top: .3rem; padding: .15rem .55rem;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1);
            border-radius: 999px; color: var(--accent-2); font-size: .66rem; font-weight: 700;
            letter-spacing: .04em; text-transform: uppercase;
        }

        .menu-link {
            display: flex; align-items: center; gap: .7rem;
            color: #94a3b8; text-decoration: none;
            padding: .55rem .7rem; margin-bottom: .2rem;
            border-radius: .6rem; font-size: .86rem; font-weight: 500;
            transition: all .18s ease; position: relative;
        }
        .menu-link .m-icon {
            width: 28px; height: 28px; border-radius: .5rem;
            background: rgba(255,255,255,.06);
            display: flex; align-items: center; justify-content: center;
            font-size: .95rem; flex-shrink: 0;
            transition: all .18s ease;
        }
        .menu-link:hover { color: #fff; background: rgba(255,255,255,.07); }
        .menu-link:hover .m-icon { background: rgba(255,255,255,.12); }
        .menu-link.active {
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff;
            box-shadow: 0 6px 18px rgba(0,0,0,.35);
        }
        .menu-link.active .m-icon { background: rgba(255,255,255,.22); }
        .m-text { flex: 1; }
        .m-badge {
            background: #ef4444; color: #fff; font-size: .64rem; font-weight: 700;
            border-radius: 999px; padding: .12rem .45rem; line-height: 1.4;
            box-shadow: 0 0 0 2px rgba(239,68,68,.25);
        }

        .menu-group { margin-bottom: .3rem; }
        .menu-group-btn {
            width: 100%; border: 0; background: transparent; color: #cbd5e1;
            display: flex; align-items: center; gap: .7rem;
            padding: .5rem .7rem; border-radius: .6rem;
            font-size: .75rem; font-weight: 700; letter-spacing: .05em; text-transform: uppercase;
            transition: all .18s ease; cursor: pointer; text-align: left;
        }
        .menu-group-btn:hover { color: #fff; background: rgba(255,255,255,.06); }
        .menu-group-btn .g-icon { color: var(--accent); font-size: 1rem; }
        .menu-group-btn .g-chev { margin-left: auto; font-size: .68rem; color: #64748b; transition: transform .25s ease; }
        .menu-group-btn.open .g-chev { transform: rotate(180deg); color: var(--accent-2); }
        .menu-group-body { max-height: 0; overflow: hidden; transition: max-height .35s ease; }
        .menu-group-body.open { max-height: 640px; }

        .sidebar-footer {
            border-top: 1px solid rgba(255,255,255,.07);
            padding: .75rem .9rem;
        }
        .profile-card {
            display: flex; align-items: center; gap: .65rem;
            background: rgba(255,255,255,.05);
            border: 1px solid rgba(255,255,255,.09);
            border-radius: .8rem; padding: .55rem .65rem;
        }
        .profile-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: .85rem; flex-shrink: 0;
            box-shadow: 0 0 0 3px rgba(255,255,255,.08);
        }
        .profile-name { color: #fff; font-size: .83rem; font-weight: 600; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .profile-role { color: #94a3b8; font-size: .7rem; }
        .logout-btn {
            background: transparent; border: 0; color: #94a3b8; font-size: 1.05rem;
            padding: .35rem .45rem; border-radius: .5rem; transition: all .18s ease; cursor: pointer;
        }
        .logout-btn:hover { color: #f87171; background: rgba(239,68,68,.14); }

        /* ═══════════ MAIN ═══════════ */
        #main { margin-left: 260px; min-height: 100vh; transition: margin .3s ease; }
        #main::before {
            content: ''; display: block; height: 3px;
            background: linear-gradient(90deg, var(--accent), var(--accent-2), transparent);
        }
        .topbar {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: .75rem 1.5rem;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        .page-content { padding: 1.5rem; }
        .stat-card { border: none; border-radius: .75rem; transition: transform .2s, box-shadow .2s; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 10px 24px rgba(0,0,0,.08); }
        .table-card { background: #fff; border-radius: .75rem; border: 1px solid #e2e8f0; }
        .badge-kritis    { background: #fee2e2; color: #dc2626; }
        .badge-aman      { background: #dcfce7; color: #16a34a; }
        .badge-overstock { background: #fef9c3; color: #ca8a04; }

        .bell-btn {
            position: relative; width: 38px; height: 38px;
            border-radius: .65rem; border: 1px solid #fee2e2;
            background: #fff1f2; color: #dc2626;
            display: flex; align-items: center; justify-content: center;
            transition: all .18s ease;
        }
        .bell-btn:hover { background: #ffe4e6; transform: translateY(-1px); }
        .user-chip {
            display: flex; align-items: center; gap: .55rem;
            border: 1px solid #e2e8f0; border-radius: .65rem;
            padding: .3rem .35rem .3rem .3rem; background: #fff;
            transition: all .18s ease;
        }
        .user-chip:hover { border-color: var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,.06); }
        .user-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), var(--accent-2));
            color: #fff; display: flex; align-items: center; justify-content: center;
            font-size: .75rem; font-weight: 700;
        }

        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); box-shadow: 0 0 40px rgba(0,0,0,.4); }
            #main { margin-left: 0; }
        }

        /* ═══════════ CETAK / PRINT ═══════════ */
        @media print {
            #sidebar, .topbar, .alert, .bell-btn, .user-chip, .no-print { display: none !important; }
            #main { margin-left: 0 !important; }
            #main::before { display: none !important; }
            .page-content { padding: 0 !important; }
            body { background: #fff !important; }
            .card, .table-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
            a { text-decoration: none !important; color: #000 !important; }
        }

        /* ═══════════ TOAST NOTIFIKASI ═══════════ */
        .toast-wrap {
            position: fixed; top: 1rem; right: 1rem; z-index: 2000;
            display: flex; flex-direction: column; gap: .6rem;
            width: min(380px, calc(100vw - 2rem));
        }
        .toast-notif {
            display: flex; align-items: flex-start; gap: .7rem;
            background: #fff; border-radius: .8rem; padding: .85rem 1rem;
            border: 1px solid #e2e8f0; border-left: 4px solid #10b981;
            box-shadow: 0 12px 32px rgba(15,23,42,.18);
            animation: toastIn .3s ease;
            transition: opacity .3s ease, transform .3s ease;
        }
        .toast-notif.toast-error { border-left-color: #ef4444; }
        .toast-notif .toast-ic { font-size: 1.3rem; color: #10b981; line-height: 1; }
        .toast-notif.toast-error .toast-ic { color: #ef4444; }
        .toast-title { font-weight: 700; font-size: .85rem; color: #0f172a; }
        .toast-msg { font-size: .8rem; color: #475569; margin-top: .1rem; }
        .toast-close {
            border: 0; background: transparent; color: #94a3b8;
            font-size: .75rem; line-height: 1; padding: .2rem; border-radius: .3rem;
            margin-left: auto; cursor: pointer; flex-shrink: 0;
        }
        .toast-close:hover { color: #ef4444; background: #fef2f2; }
        .toast-notif.toast-hide { opacity: 0; transform: translateX(1rem); }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(1rem); }
            to   { opacity: 1; transform: translateX(0); }
        }
        @media print { .toast-wrap { display: none !important; } }
    </style>
    @stack('styles')
</head>
<body class="role-{{ auth()->user()->role }}">

@php
    $jumlahKritis = \App\Models\Barang::where('is_active', true)
        ->whereRaw('stok_saat_ini <= stok_minimum')->count();
    $stokOverstock = \App\Models\Barang::where('is_active', true)
        ->whereRaw('stok_saat_ini >= stok_maksimum')
        ->where('stok_maksimum', '>', 0)->count();
    $user = auth()->user();
    $initial = strtoupper(substr($user->name, 0, 1));
    // Permintaan gudang & PO menunggu aksi Pemilik (Setujui / Tolak)
    $jumlahMenungguPO = $user->isAdmin()
        ? \App\Models\PurchaseOrder::whereIn('status', ['permintaan', 'menunggu_persetujuan'])->count()
        : 0;
@endphp

<nav id="sidebar">
    <div class="sidebar-brand d-flex align-items-center gap-3">
        <div class="brand-logo">
            <i class="bi bi-boxes"></i>
        </div>
        <div>
            <h6>Mitra Mandiri</h6>
            <small>Sistem Stok Gudang</small>
            <span class="brand-chip"><i class="bi bi-shield-check"></i> {{ $user->role_label }}</span>
        </div>
    </div>

    <div class="sidebar-body">

        {{-- Menu Utama (semua role) --}}
        <a href="{{ route('landing') }}" class="menu-link {{ request()->routeIs('landing') ? 'active' : '' }}">
            <span class="m-icon"><i class="bi bi-speedometer2"></i></span>
            <span class="m-text">Dashboard</span>
        </a>

        {{-- ══════════ ADMIN (Owner) — monitoring saja ══════════ --}}
        @if($user->isAdmin())

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-admin-monitor" aria-expanded="false" aria-controls="grp-admin-monitor">
                    <span class="g-icon"><i class="bi bi-eye"></i></span> Monitoring
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-admin-monitor">
                    <a href="{{ route('barang.index') }}" class="menu-link {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-box-seam"></i></span>
                        <span class="m-text">Barang</span>
                    </a>
                    <a href="{{ route('supplier.index') }}" class="menu-link {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-truck"></i></span>
                        <span class="m-text">Supplier</span>
                    </a>
                    <a href="{{ route('stok-masuk.index') }}" class="menu-link {{ request()->routeIs('stok-masuk.index') || request()->routeIs('stok-masuk.show') || request()->routeIs('stok-masuk.create') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                        <span class="m-text">Stok Masuk</span>
                    </a>
                    <a href="{{ route('stok-masuk.riwayat') }}" class="menu-link {{ request()->routeIs('stok-masuk.riwayat') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Barang Masuk</span>
                    </a>
                    <a href="{{ route('piutang.index') }}" class="menu-link {{ request()->routeIs('piutang.index') || request()->routeIs('piutang.show') || request()->routeIs('piutang.laporan') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-credit-card"></i></span>
                        <span class="m-text">Data Piutang</span>
                    </a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button" class="menu-group-btn {{ request()->routeIs('purchase-order.*') ? 'open' : '' }}" data-target="grp-admin-pembelian" aria-expanded="false" aria-controls="grp-admin-pembelian">
                    <span class="g-icon"><i class="bi bi-cart-plus"></i></span> Pembelian
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body {{ request()->routeIs('purchase-order.*') ? 'open' : '' }}" id="grp-admin-pembelian">
                    {{-- Admin/Owner menyetujui permintaan dari gudang --}}
                    <a href="{{ route('purchase-order.index') }}" class="menu-link {{ request()->routeIs('purchase-order.index') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-list-ul"></i></span>
                        <span class="m-text">Daftar Purchase Order</span>
                        @if($jumlahMenungguPO > 0)<span class="m-badge" title="Permintaan menunggu dibuatkan PO">{{ $jumlahMenungguPO }}</span>@endif
                    </a>
                    <a href="{{ route('purchase-order.laporan') }}" class="menu-link {{ request()->routeIs('purchase-order.laporan') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-bar-chart-line"></i></span>
                        <span class="m-text">Laporan PO</span>
                    </a>
                    <a href="{{ route('purchase-order.riwayat') }}" class="menu-link {{ request()->routeIs('purchase-order.riwayat') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Pembelian</span>
                    </a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-admin-laporan" aria-expanded="false" aria-controls="grp-admin-laporan">
                    <span class="g-icon"><i class="bi bi-bar-chart-line"></i></span> Laporan
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-admin-laporan">
                    <a href="{{ route('laporan.index') }}" class="menu-link {{ request()->routeIs('laporan.index') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-house-gear"></i></span>
                        <span class="m-text">Pusat Laporan</span>
                    </a>
                    <a href="{{ route('laporan.stok') }}" class="menu-link {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clipboard-data"></i></span>
                        <span class="m-text">Laporan Stok</span>
                    </a>
                    <a href="{{ route('laporan.stok-kritis') }}" class="menu-link {{ request()->routeIs('laporan.stok-kritis') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-exclamation-triangle"></i></span>
                        <span class="m-text">Stok Kritis</span>
                        @if($jumlahKritis > 0)<span class="m-badge">{{ $jumlahKritis }}</span>@endif
                    </a>
                    <a href="{{ route('laporan.overstok') }}" class="menu-link {{ request()->routeIs('laporan.overstok') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-exclamation-diamond"></i></span>
                        <span class="m-text">Stok Overstok</span>
                        @if($stokOverstock > 0)<span class="m-badge" style="background:#d97706">{{ $stokOverstock }}</span>@endif
                    </a>
                    <a href="{{ route('laporan.mutasi') }}" class="menu-link {{ request()->routeIs('laporan.mutasi') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-arrow-left-right"></i></span>
                        <span class="m-text">Mutasi Barang</span>
                    </a>
                    <a href="{{ route('laporan.mutasi-detail') }}" class="menu-link {{ request()->routeIs('laporan.mutasi-detail') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Mutasi Stok</span>
                    </a>
                    <a href="{{ route('laporan.penjualan') }}" class="menu-link {{ request()->routeIs('laporan.penjualan') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-graph-up"></i></span>
                        <span class="m-text">Penjualan</span>
                    </a>
                    {{-- Laporan Piutang sementara di-nonaktifkan --}}
                    {{--
                    <a href="{{ route('piutang.laporan') }}" class="menu-link {{ request()->routeIs('piutang.laporan') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-cash-stack"></i></span>
                        <span class="m-text">Laporan Piutang</span>
                    </a>
                    --}}
                </div>
            </div>

        {{-- ══════════ BAGIAN GUDANG ══════════ --}}
        @elseif($user->isGudang())

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-gudang-master" aria-expanded="false" aria-controls="grp-gudang-master">
                    <span class="g-icon"><i class="bi bi-database"></i></span> Barang
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-gudang-master">
                    <a href="{{ route('barang.index') }}" class="menu-link {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-box-seam"></i></span>
                        <span class="m-text">Barang</span>
                    </a>
                    <a href="{{ route('supplier.index') }}" class="menu-link {{ request()->routeIs('supplier.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-truck"></i></span>
                        <span class="m-text">Supplier</span>
                    </a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-gudang-transaksi" aria-expanded="false" aria-controls="grp-gudang-transaksi">
                    <span class="g-icon"><i class="bi bi-arrow-left-right"></i></span> Transaksi
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-gudang-transaksi">
                    <a href="{{ route('stok-masuk.index') }}" class="menu-link {{ request()->routeIs('stok-masuk.index') || request()->routeIs('stok-masuk.show') || request()->routeIs('stok-masuk.create') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                        <span class="m-text">Stok Masuk</span>
                    </a>
                    <a href="{{ route('stok-masuk.riwayat') }}" class="menu-link {{ request()->routeIs('stok-masuk.riwayat') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Barang Masuk</span>
                    </a>
                    <a href="{{ route('retur.index') }}" class="menu-link {{ request()->routeIs('retur.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-arrow-return-left"></i></span>
                        <span class="m-text">Retur Barang</span>
                    </a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button" class="menu-group-btn {{ request()->routeIs('purchase-order.*') ? 'open' : '' }}" data-target="grp-gudang-pembelian" aria-expanded="false" aria-controls="grp-gudang-pembelian">
                    <span class="g-icon"><i class="bi bi-cart-plus"></i></span> Pembelian
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body {{ request()->routeIs('purchase-order.*') ? 'open' : '' }}" id="grp-gudang-pembelian">
                    <a href="{{ route('purchase-order.create') }}" class="menu-link {{ request()->routeIs('purchase-order.create') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-plus-circle"></i></span>
                        <span class="m-text">Pesan Barang Kritis</span>
                    </a>
                    <a href="{{ route('purchase-order.index') }}" class="menu-link {{ request()->routeIs('purchase-order.index') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-list-ul"></i></span>
                        <span class="m-text">Daftar Purchase Order</span>
                    </a>
                    <a href="{{ route('purchase-order.penerimaan') }}" class="menu-link {{ request()->routeIs('purchase-order.penerimaan') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-box-arrow-in-down"></i></span>
                        <span class="m-text">Penerimaan Barang</span>
                    </a>
                    <a href="{{ route('purchase-order.riwayat') }}" class="menu-link {{ request()->routeIs('purchase-order.riwayat') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Pembelian</span>
                    </a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-gudang-laporan" aria-expanded="false" aria-controls="grp-gudang-laporan">
                    <span class="g-icon"><i class="bi bi-bar-chart-line"></i></span> Laporan
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-gudang-laporan">
                    <a href="{{ route('laporan.index') }}" class="menu-link {{ request()->routeIs('laporan.index') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-house-gear"></i></span>
                        <span class="m-text">Pusat Laporan</span>
                    </a>
                    <a href="{{ route('laporan.stok') }}" class="menu-link {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clipboard-data"></i></span>
                        <span class="m-text">Laporan Stok</span>
                    </a>
                    <a href="{{ route('laporan.stok-kritis') }}" class="menu-link {{ request()->routeIs('laporan.stok-kritis') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-exclamation-triangle"></i></span>
                        <span class="m-text">Stok Kritis</span>
                        @if($jumlahKritis > 0)<span class="m-badge">{{ $jumlahKritis }}</span>@endif
                    </a>
                    <a href="{{ route('laporan.overstok') }}" class="menu-link {{ request()->routeIs('laporan.overstok') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-exclamation-diamond"></i></span>
                        <span class="m-text">Stok Overstok</span>
                        @if($stokOverstock > 0)<span class="m-badge" style="background:#d97706">{{ $stokOverstock }}</span>@endif
                    </a>
                    <a href="{{ route('laporan.mutasi') }}" class="menu-link {{ request()->routeIs('laporan.mutasi') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-arrow-left-right"></i></span>
                        <span class="m-text">Mutasi Barang</span>
                    </a>
                    <a href="{{ route('laporan.mutasi-detail') }}" class="menu-link {{ request()->routeIs('laporan.mutasi-detail') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Mutasi Stok</span>
                    </a>
                </div>
            </div>

        {{-- ══════════ KASIR ══════════ --}}
        @elseif($user->isKasir())

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-kasir-transaksi" aria-expanded="false" aria-controls="grp-kasir-transaksi">
                    <span class="g-icon"><i class="bi bi-cart-check"></i></span> Transaksi
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-kasir-transaksi">
                    <a href="{{ route('pos.index') }}" class="menu-link {{ request()->routeIs('pos.index') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-bag-check"></i></span>
                        <span class="m-text">Kasir</span>
                    </a>
                    <a href="{{ route('pos.riwayat') }}" class="menu-link {{ request()->routeIs('pos.riwayat') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clock-history"></i></span>
                        <span class="m-text">Riwayat Transaksi</span>
                    </a>
                    <a href="{{ route('retur.index') }}" class="menu-link {{ request()->routeIs('retur.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-arrow-return-left"></i></span>
                        <span class="m-text">Retur Barang</span>
                    </a>
                </div>
            </div>

            {{-- Stok Barang sementara di-nonaktifkan untuk kasir --}}
            {{--
            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-kasir-stok" aria-expanded="false" aria-controls="grp-kasir-stok">
                    <span class="g-icon"><i class="bi bi-box-seam"></i></span> Stok Barang
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-kasir-stok">
                    <a href="{{ route('barang.index') }}" class="menu-link {{ request()->routeIs('barang.*') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-box-seam"></i></span>
                        <span class="m-text">Barang</span>
                    </a>
                </div>
            </div>
            --}}

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-kasir-piutang" aria-expanded="false" aria-controls="grp-kasir-piutang">
                    <span class="g-icon"><i class="bi bi-credit-card"></i></span> Piutang
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-kasir-piutang">
                    <a href="{{ route('piutang.index') }}" class="menu-link {{ request()->routeIs('piutang.index') || request()->routeIs('piutang.show') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-credit-card"></i></span>
                        <span class="m-text">Data Piutang</span>
                    </a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button" class="menu-group-btn" data-target="grp-kasir-laporan" aria-expanded="false" aria-controls="grp-kasir-laporan">
                    <span class="g-icon"><i class="bi bi-bar-chart-line"></i></span> Laporan
                    <i class="bi bi-chevron-down g-chev"></i>
                </button>
                <div class="menu-group-body" id="grp-kasir-laporan">
                    <a href="{{ route('laporan.stok') }}" class="menu-link {{ request()->routeIs('laporan.stok') ? 'active' : '' }}">
                        <span class="m-icon"><i class="bi bi-clipboard-data"></i></span>
                        <span class="m-text">Laporan Stok</span>
                    </a>
                </div>
            </div>

        @endif
    </div>

    {{-- Profil & Logout --}}
    <div class="sidebar-footer">
        <div class="profile-card">
            <div class="profile-avatar">{{ $initial }}</div>
            <div class="flex-grow-1" style="min-width:0">
                <div class="profile-name">{{ $user->name }}</div>
                <div class="profile-role">{{ $user->role_label }}</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="logout-btn" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                </button>
            </form>
        </div>
    </div>
</nav>

<div id="main">
    <div class="topbar d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle" type="button">
                <i class="bi bi-list fs-5"></i>
            </button>
            <nav aria-label="breadcrumb" class="d-none d-md-block">
                <ol class="breadcrumb mb-0 small">
                    @yield('breadcrumb')
                </ol>
            </nav>
        </div>

        <div class="d-flex align-items-center gap-2">
            @if($jumlahKritis > 0)
            <a href="{{ route('laporan.stok-kritis') }}" class="bell-btn" title="Stok kritis">
                <i class="bi bi-bell-fill"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-2 border-white">
                    {{ $jumlahKritis }}
                </span>
            </a>
            @endif

            <div class="dropdown">
                <button class="user-chip dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar">{{ $initial }}</div>
                    <span class="d-none d-sm-inline small fw-semibold">{{ $user->name }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                    <li class="px-3 py-2">
                        <div class="small fw-bold">{{ $user->name }}</div>
                        <div class="small text-muted">{{ $user->role_label }}</div>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.index') }}">
                            <i class="bi bi-person-circle me-2"></i>Profil Saya
                        </a>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i>Keluar
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="page-content">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Kompatibel dengan browser lama (ES5, tanpa optional chaining / arrow function)
document.addEventListener('DOMContentLoaded', function () {
    var sidebar = document.getElementById('sidebar');

    // Tombol hamburger (mobile)
    var sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // Di layar kecil: tutup sidebar setelah LINK menu diklik (bukan judul grup, agar grup tetap bisa dibuka)
    var closeable = document.querySelectorAll('.menu-link');
    for (var i = 0; i < closeable.length; i++) {
        closeable[i].addEventListener('click', function () {
            if (window.innerWidth <= 768 && sidebar) sidebar.classList.remove('show');
        });
    }

    // Menu grup bisa dilipat (accordion)
    var groupBtns = document.querySelectorAll('.menu-group-btn');
    for (var g = 0; g < groupBtns.length; g++) {
        (function (btn) {
            btn.addEventListener('click', function () {
                var target = document.getElementById(btn.getAttribute('data-target'));
                if (!target) return;
                var isOpen = target.classList.contains('open');
                var bodies = document.querySelectorAll('.menu-group-body');
                var btns = document.querySelectorAll('.menu-group-btn');
                for (var b = 0; b < bodies.length; b++) bodies[b].classList.remove('open');
                for (var c = 0; c < btns.length; c++) btns[c].classList.remove('open');
                if (!isOpen) {
                    target.classList.add('open');
                    btn.classList.add('open');
                    btn.setAttribute('aria-expanded', 'true');
                } else {
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        })(groupBtns[g]);
    }

    // Buka otomatis grup yang berisi menu aktif
    var activeLinks = document.querySelectorAll('.menu-link.active');
    for (var a = 0; a < activeLinks.length; a++) {
        var body = activeLinks[a].closest('.menu-group-body');
        if (body) {
            body.classList.add('open');
            var openBtn = document.querySelector('.menu-group-btn[data-target="' + body.id + '"]');
            if (openBtn) {
                openBtn.classList.add('open');
                openBtn.setAttribute('aria-expanded', 'true');
            }
        }
    }
});
</script>

{{-- Notifikasi toast: pesan sukses/gagal dari session --}}
<div class="toast-wrap" id="toastWrap">
    @if(session('success'))
    <div class="toast-notif" role="alert">
        <div class="toast-ic"><i class="bi bi-check-circle-fill"></i></div>
        <div class="flex-grow-1">
            <div class="toast-title">Berhasil</div>
            <div class="toast-msg">{{ session('success') }}</div>
        </div>
        <button type="button" class="toast-close" aria-label="Tutup" onclick="tutupToast(this)"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif
    @if(session('error'))
    <div class="toast-notif toast-error" role="alert">
        <div class="toast-ic"><i class="bi bi-x-circle-fill"></i></div>
        <div class="flex-grow-1">
            <div class="toast-title">Gagal</div>
            <div class="toast-msg">{{ session('error') }}</div>
        </div>
        <button type="button" class="toast-close" aria-label="Tutup" onclick="tutupToast(this)"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif
    @if(session('warning'))
    <div class="toast-notif" style="border-left-color:#f59e0b" role="alert">
        <div class="toast-ic" style="color:#f59e0b"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="flex-grow-1">
            <div class="toast-title">Perhatian</div>
            <div class="toast-msg">{{ session('warning') }}</div>
        </div>
        <button type="button" class="toast-close" aria-label="Tutup" onclick="tutupToast(this)"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif
    @if(session('info'))
    <div class="toast-notif" style="border-left-color:#3b82f6" role="alert">
        <div class="toast-ic" style="color:#3b82f6"><i class="bi bi-info-circle-fill"></i></div>
        <div class="flex-grow-1">
            <div class="toast-title">Informasi</div>
            <div class="toast-msg">{{ session('info') }}</div>
        </div>
        <button type="button" class="toast-close" aria-label="Tutup" onclick="tutupToast(this)"><i class="bi bi-x-lg"></i></button>
    </div>
    @endif
</div>

<script>
// Toast notifikasi: tutup manual & auto-hide setelah 5 detik (ES5)
function tutupToast(btn) {
    var toast = btn.closest('.toast-notif');
    if (!toast) return;
    toast.classList.add('toast-hide');
    setTimeout(function () {
        if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 300);
}
document.addEventListener('DOMContentLoaded', function () {
    var toasts = document.querySelectorAll('.toast-wrap .toast-notif');
    for (var i = 0; i < toasts.length; i++) {
        (function (toast) {
            setTimeout(function () {
                if (!toast.parentNode) return;
                toast.classList.add('toast-hide');
                setTimeout(function () {
                    if (toast.parentNode) toast.parentNode.removeChild(toast);
                }, 300);
            }, 5000);
        })(toasts[i]);
    }
});
</script>
@stack('scripts')
</body>
</html>
