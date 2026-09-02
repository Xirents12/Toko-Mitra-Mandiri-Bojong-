@extends('layouts.app')

@section('title', 'Stok Masuk')
@section('page-title', 'Stok Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Data Stok Masuk</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('stok-masuk.riwayat') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1"></i> Riwayat Barang Masuk
        </a>
        <a href="{{ route('stok-masuk.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Catat Stok Masuk
        </a>
    </div>
</div>

{{-- Filter --}}
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
                <a href="{{ route('stok-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
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
                        <th>#</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th>No. Nota</th>
                        <th class="text-center">Jml Item</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-center" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokMasuks as $sm)
                    <tr>
                        <td>{{ $stokMasuks->firstItem() + $loop->index }}</td>
                        <td><code>{{ $sm->no_transaksi }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($sm->tanggal_masuk)->format('d/m/Y') }}</td>
                        <td>{{ $sm->supplier->nama_supplier ?? '-' }}</td>
                        <td>{{ $sm->no_nota_supplier ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $sm->details->count() }} item</span>
                        </td>
                        <td>{{ $sm->user->name ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('stok-masuk.show', $sm->id) }}"
                               class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('stok-masuk.destroy', $sm->id) }}"
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
                            Belum ada data stok masuk.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($stokMasuks->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $stokMasuks->firstItem() }}–{{ $stokMasuks->lastItem() }}
            dari {{ $stokMasuks->total() }} data
        </small>
        {{ $stokMasuks->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection