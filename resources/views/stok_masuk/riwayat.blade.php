@extends('layouts.app')

@section('title', 'Riwayat Barang Masuk')
@section('page-title', 'Riwayat Barang Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Riwayat Barang Masuk / Stok Masuk</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('stok-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-list-ul me-1"></i> Data Stok Masuk
        </a>
        <a href="{{ route('stok-masuk.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Catat Stok Masuk
        </a>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="mb-1 small text-muted">Total Transaksi</p>
                    <h5 class="fw-bold mb-0">{{ number_format($totalTransaksi) }}</h5>
                </div>
                <i class="bi bi-receipt fs-2 text-success opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="mb-1 small text-muted">Total Qty Masuk</p>
                    <h5 class="fw-bold mb-0">{{ number_format($totalQty) }}</h5>
                </div>
                <i class="bi bi-box-arrow-in-down fs-2 text-primary opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="mb-1 small text-muted">Total Nilai</p>
                    <h5 class="fw-bold mb-0 text-success">Rp {{ number_format($totalNilai, 0, ',', '.') }}</h5>
                </div>
                <i class="bi bi-cash-stack fs-2 text-warning opacity-50"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card card border-0 shadow-sm h-100">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="mb-1 small text-muted">Jenis Barang</p>
                    <h5 class="fw-bold mb-0">{{ number_format($totalJenis) }}</h5>
                </div>
                <i class="bi bi-box-seam fs-2 text-info opacity-50"></i>
            </div>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1 text-muted">Cari (No. Transaksi / Barang)</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="No. transaksi, nama atau kode barang..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1 text-muted">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">-- Semua Supplier --</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>
                            {{ $sup->nama_supplier }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1 text-muted">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control form-control-sm"
                    value="{{ request('dari') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1 text-muted">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control form-control-sm"
                    value="{{ request('sampai') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100" title="Filter">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('stok-masuk.riwayat') }}" class="btn btn-outline-secondary btn-sm" title="Reset">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel Riwayat --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>No. Transaksi</th>
                        <th>Barang</th>
                        <th>Supplier</th>
                        <th class="text-center">Jumlah</th>
                        <th class="text-end">Harga Beli</th>
                        <th class="text-end">Subtotal</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayats as $r)
                    <tr>
                        <td>{{ $riwayats->firstItem() + $loop->index }}</td>
                        <td class="text-nowrap">
                            {{ \Carbon\Carbon::parse($r->stokMasuk->tanggal_masuk)->format('d/m/Y') }}
                        </td>
                        <td>
                            <a href="{{ route('stok-masuk.show', $r->stok_masuk_id) }}"
                               title="{{ $r->stokMasuk->keterangan ?? 'Lihat detail transaksi' }}">
                                <code>{{ $r->stokMasuk->no_transaksi }}</code>
                            </a>
                        </td>
                        <td>
                            {{ $r->barang->nama_barang ?? '-' }}
                            <small class="text-muted d-block">{{ $r->barang->kode_barang ?? '' }}</small>
                        </td>
                        <td>{{ $r->stokMasuk->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">
                                {{ number_format($r->jumlah) }} {{ $r->barang->satuan ?? '' }}
                            </span>
                        </td>
                        <td class="text-end">Rp {{ number_format($r->harga_beli, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">
                            Rp {{ number_format($r->jumlah * $r->harga_beli, 0, ',', '.') }}
                        </td>
                        <td>{{ $r->stokMasuk->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada riwayat barang masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($riwayats->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $riwayats->firstItem() }}–{{ $riwayats->lastItem() }}
            dari {{ $riwayats->total() }} baris
        </small>
        {{ $riwayats->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
