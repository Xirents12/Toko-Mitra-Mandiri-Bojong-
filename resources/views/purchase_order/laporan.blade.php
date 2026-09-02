@extends('layouts.app')

@section('title', 'Laporan Purchase Order')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Laporan Purchase Order</h5>
    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Dari Tanggal</label>
                <input type="date" name="dari" class="form-control form-control-sm" value="{{ request('dari') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" class="form-control form-control-sm" value="{{ request('sampai') }}">
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
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach(\App\Models\PurchaseOrder::STATUS_LABEL as $k => $v)
                    <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-12">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
                <a href="{{ route('purchase-order.laporan') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <small class="text-muted">Total PO</small>
                <h4 class="fw-bold mb-0">{{ $totalPO }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <small class="text-muted">Total Nilai PO</small>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalNilai, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card">
            <div class="card-body">
                <small class="text-muted">Total Barang Diterima</small>
                <h4 class="fw-bold mb-0">{{ $totalDiterima }} item</h4>
            </div>
        </div>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span>Rincian Purchase Order</span>
        <a href="javascript:window.print()" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-printer me-1"></i> Print
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelLaporan" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Diterima</th>
                        <th class="text-center">Status</th>
                        <th>Dibuat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $po)
                    <tr>
                        <td><code>{{ $po->no_po }}</code></td>
                        <td>{{ $po->tanggal_po->format('d/m/Y') }}</td>
                        <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-end">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $po->total_diterima }}/{{ $po->total_dipesan }}</td>
                        <td class="text-center"><span class="badge bg-{{ $po->status_color }}">{{ $po->status_label }}</span></td>
                        <td><small>{{ $po->user->name ?? '-' }}</small></td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>Tidak ada data untuk filter ini.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script>
$(document).ready(function () {
    if ($.fn.dataTable && $('#tabelLaporan tbody tr').length) {
        $('#tabelLaporan').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json' },
            dom: '<"row align-items-center mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-md-end"B>>' +
                 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-outline-success' },
                { extend: 'pdfHtml5', text: '<i class="bi bi-file-earmark-pdf"></i> PDF', className: 'btn btn-sm btn-outline-danger' },
                { extend: 'print', text: '<i class="bi bi-printer"></i> Print', className: 'btn btn-sm btn-outline-primary' }
            ],
            order: [[0, 'desc']]
        });
    }
});
</script>
@endpush
