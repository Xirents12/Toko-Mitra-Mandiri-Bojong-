@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Supplier</h5>
    <a href="{{ route('supplier.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Tambah Supplier
    </a>
</div>

{{-- Statistik --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card bg-primary text-white">
            <div class="card-body">
                <small class="opacity-75">Total Supplier</small>
                <h4 class="fw-bold mb-0">{{ $totalSupplier }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card bg-success text-white">
            <div class="card-body">
                <small class="opacity-75">Supplier Aktif</small>
                <h4 class="fw-bold mb-0">{{ $totalAktif }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card bg-secondary text-white">
            <div class="card-body">
                <small class="opacity-75">Supplier Nonaktif</small>
                <h4 class="fw-bold mb-0">{{ $totalNonaktif }}</h4>
            </div>
        </div>
    </div>
</div>

{{-- Filter & Search --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari kode / nama / telepon / email supplier..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="aktif"    {{ request('status') == 'aktif'    ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Kode</th>
                        <th>Nama Supplier</th>
                        <th>Kontak</th>
                        <th>Alamat</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" width="140">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr>
                        <td>{{ $suppliers->firstItem() + $loop->index }}</td>
                        <td><code>{{ $supplier->kode_supplier }}</code></td>
                        <td class="fw-semibold">{{ $supplier->nama_supplier }}</td>
                        <td>
                            @if($supplier->telepon)
                            <i class="bi bi-telephone me-1 text-muted small"></i>{{ $supplier->telepon }}<br>
                            @endif
                            @if($supplier->email)
                            <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $supplier->email }}</small>
                            @endif
                        </td>
                        <td class="small">{{ $supplier->alamat ?: '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $supplier->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $supplier->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('supplier.edit', $supplier->id) }}"
                               class="btn btn-outline-warning btn-sm" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('supplier.destroy', $supplier->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus supplier ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" title="Hapus">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada data supplier.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($suppliers->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $suppliers->firstItem() }}–{{ $suppliers->lastItem() }}
            dari {{ $suppliers->total() }} supplier
        </small>
        {{ $suppliers->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
