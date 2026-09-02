@extends('layouts.app')

@section('title', 'Penerimaan Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2 text-success"></i>Penerimaan Barang</h5>
    <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="alert alert-success border-0 shadow-sm">
    <i class="bi bi-info-circle me-1"></i>
    Daftar Purchase Order yang sudah <b>Disetujui</b>, <b>Dikirim Supplier</b>, atau <b>Diterima Sebagian</b> — siap diproses penerimaan barang.
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
                        <th class="text-center">Progress</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    <tr>
                        <td><code>{{ $po->no_po }}</code></td>
                        <td>{{ $po->tanggal_po->format('d/m/Y') }}</td>
                        <td>{{ $po->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-center">
                            <small>{{ $po->total_diterima }}/{{ $po->total_dipesan }} item</small>
                            <div class="progress mt-1 mx-auto" style="max-width:140px;height:6px;">
                                <div class="progress-bar bg-success"
                                     style="width: {{ $po->total_dipesan > 0 ? round($po->total_diterima / $po->total_dipesan * 100) : 0 }}%"></div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $po->status_color }}">{{ $po->status_label }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('purchase-order.terima', $po->id) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-box-arrow-in-down me-1"></i> Terima Barang
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>Tidak ada PO yang menunggu penerimaan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($pos->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <small class="text-muted">{{ $pos->firstItem() }}–{{ $pos->lastItem() }} dari {{ $pos->total() }}</small>
        {{ $pos->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
