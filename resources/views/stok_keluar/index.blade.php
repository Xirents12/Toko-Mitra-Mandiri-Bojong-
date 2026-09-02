@extends('layouts.app')

@section('title', 'Stok Keluar')
@section('page-title', 'Stok Keluar')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Data Stok Keluar</h5>
    <a href="{{ route('stok-keluar.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Catat Stok Keluar
    </a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari no. transaksi..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                    value="{{ request('tanggal_awal') }}">
            </div>
            <div class="col-md-3">
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                    value="{{ request('tanggal_akhir') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('stok-keluar.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Penerima</th>
                        <th>Jenis</th>
                        <th class="text-center">Jml Item</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-center" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokKeluars as $sk)
                    <tr>
                        <td>{{ $stokKeluars->firstItem() + $loop->index }}</td>
                        <td><code>{{ $sk->no_transaksi }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($sk->tanggal_keluar)->format('d/m/Y') }}</td>
                        <td>{{ $sk->nama_pelanggan ?? '-' }}</td>
                        <td>
                            <span class="badge bg-secondary">
                                {{ ucfirst($sk->jenis_keluar ?? '-') }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ $sk->details->count() }} item</span>
                        </td>
                        <td>{{ $sk->user->name ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('stok-keluar.show', $sk->id) }}"
                               class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('stok-keluar.destroy', $sk->id) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus data ini? Stok barang akan dikembalikan.')">
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
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada data stok keluar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($stokKeluars->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $stokKeluars->firstItem() }}–{{ $stokKeluars->lastItem() }}
            dari {{ $stokKeluars->total() }} data
        </small>
        {{ $stokKeluars->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection