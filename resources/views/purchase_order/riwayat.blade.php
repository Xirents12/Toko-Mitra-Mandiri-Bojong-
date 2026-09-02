@extends('layouts.app')

@section('title', 'Riwayat Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Riwayat Pembelian</h5>
    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Cari No. PO</label>
                <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Cari...">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->nama_supplier }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach(\App\Models\PurchaseOrder::STATUS_LABEL as $k => $v)
                    <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Dari</label>
                <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small mb-1">Sampai</label>
                <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
            </div>
            <div class="col-md-12 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('purchase-order.riwayat') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i> Reset</a>
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
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Diterima</th>
                        <th class="text-center">Status</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($riwayats as $po)
                    <tr>
                        <td><code>{{ $po->no_po }}</code></td>
                        <td>{{ $po->tanggal_po->format('d/m/Y') }}</td>
                        <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $po->total_diterima }}/{{ $po->total_dipesan }}</td>
                        <td class="text-center"><span class="badge bg-{{ $po->status_color }}">{{ $po->status_label }}</span></td>
                        <td><small>{{ $po->user->name ?? '-' }}</small></td>
                        <td class="text-center">
                            <a href="{{ route('purchase-order.show', $po->id) }}" class="btn btn-outline-info btn-sm"><i class="bi bi-eye"></i></a>
                            <a href="{{ route('purchase-order.cetak', $po->id) }}" target="_blank" class="btn btn-outline-primary btn-sm"><i class="bi bi-printer"></i></a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>Belum ada riwayat pembelian.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($riwayats->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $riwayats->firstItem() }}–{{ $riwayats->lastItem() }} dari {{ $riwayats->total() }}</small>
        {{ $riwayats->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
