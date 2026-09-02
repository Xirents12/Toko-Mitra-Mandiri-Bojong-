@extends('layouts.app')

@section('title', 'Riwayat Retur ke Supplier')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">Riwayat Retur ke Supplier</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('retur.index', ['tab' => 'supplier']) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-arrow-return-left me-1"></i> Retur ke Supplier
        </a>
        <a href="{{ route('retur.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Retur
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Cari No. Transaksi</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="RS-..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">Semua Supplier</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->nama_supplier }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('retur.supplier-riwayat') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body py-3">
                <div class="text-muted small">Total Transaksi</div>
                <div class="fw-bold fs-5">{{ number_format($totalTransaksi, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body py-3">
                <div class="text-muted small">Total Barang Diretur</div>
                <div class="fw-bold fs-5">{{ number_format($totalQty, 0, ',', '.') }} unit</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body py-3">
                <div class="text-muted small">Total Nilai Retur</div>
                <div class="fw-bold fs-5 text-danger">Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
            </div>
        </div>
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
                        <th class="text-center">Jml Item</th>
                        <th class="text-end">Total Nilai</th>
                        <th>Alasan</th>
                        <th>Dicatat Oleh</th>
                        <th class="text-center" width="100">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returSuppliers as $r)
                    @php
                        $alasanList = $r->details->pluck('keterangan')
                            ->map(fn($k) => preg_match('/Alasan:\s*(.+?)\s*$/', (string) $k, $m) ? $m[1] : '')
                            ->filter()->unique()->values();
                    @endphp
                    <tr>
                        <td>{{ $returSuppliers->firstItem() + $loop->index }}</td>
                        <td><code>{{ $r->no_transaksi }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($r->tanggal_keluar)->format('d/m/Y') }}</td>
                        <td>{{ $r->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ $r->details->count() }} item</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                        <td>
                            @if($alasanList->count())
                            <span class="text-muted small" title="{{ $alasanList->implode(', ') }}">
                                {{ $alasanList->take(2)->implode(', ') }}{{ $alasanList->count() > 2 ? ', dll.' : '' }}
                            </span>
                            @else
                            <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>{{ $r->user->name ?? '-' }}</td>
                        <td class="text-center">
                            <a href="{{ route('retur.supplier-show', $r->id) }}"
                               class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                            <form action="{{ route('retur.supplier-destroy', $r->id) }}" method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Batalkan retur ini? Stok barang akan dikembalikan.')">
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
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada data retur ke supplier.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($returSuppliers->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">
            Menampilkan {{ $returSuppliers->firstItem() }}–{{ $returSuppliers->lastItem() }}
            dari {{ $returSuppliers->total() }} data
        </small>
        {{ $returSuppliers->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
