@extends('layouts.app')

@section('title', 'Daftar Purchase Order')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-cart-plus me-2 text-primary"></i>Daftar Purchase Order</h5>
    @if(auth()->user()->isGudang())
    <a href="{{ route('purchase-order.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Pesan Barang Kritis (Permintaan)
    </a>
    @endif
</div>

{{-- ═══ NOTIFIKASI STOK KRITIS ═══ --}}
@if($barangKritis->isNotEmpty())
<div class="alert alert-danger border-0 shadow-sm mb-3">
    <div class="d-flex align-items-start gap-2">
        <i class="bi bi-exclamation-triangle-fill fs-4 mt-1"></i>
        <div class="flex-grow-1">
            <strong>Stok barang berikut sudah mencapai batas minimum dan disarankan untuk dibuatkan Purchase Order:</strong>
            <div class="mt-2 d-flex flex-wrap gap-1">
                @foreach($barangKritis as $b)
                @if(auth()->user()->isGudang())
                <label class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle d-inline-flex align-items-center gap-1 py-1 mb-0" style="cursor:pointer">
                    <input type="checkbox" class="form-check-input check-item m-0" value="{{ $b->id }}"
                           data-nama="{{ $b->nama_barang }}">
                    {{ $b->nama_barang }} <small>(sisa {{ $b->stok_saat_ini }} {{ $b->satuan }} / min {{ $b->stok_minimum }})</small>
                </label>
                @else
                <span class="badge bg-danger-subtle text-danger-emphasis border border-danger-subtle">
                    {{ $b->nama_barang }} <small>(sisa {{ $b->stok_saat_ini }} {{ $b->satuan }} / min {{ $b->stok_minimum }})</small>
                </span>
                @endif
                @endforeach
            </div>
            @if(auth()->user()->isGudang())
            <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                <label class="small mb-0 me-1" style="cursor:pointer">
                    <input type="checkbox" class="form-check-input me-1" id="pilihSemua"> Pilih semua
                </label>
                <form action="{{ route('purchase-order.auto-kritis') }}" method="POST" class="d-inline-block"
                      onsubmit="return confirm('Buat permintaan dari SEMUA barang stok kritis? Permintaan dibuat per supplier dan dikirim ke admin/owner untuk disetujui.')">
                    @csrf
                    <button class="btn btn-danger btn-sm">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Pesan Barang Kritis (Semua)
                    </button>
                </form>
                <form action="{{ route('purchase-order.auto-kritis') }}" method="POST" class="d-inline-block" id="formPesanTerpilih">
                    @csrf
                    <input type="hidden" name="barang_ids" id="barangIdsTerpilih" value="">
                    <button type="submit" id="btnPesanTerpilih" class="btn btn-outline-danger btn-sm" disabled
                            onclick="return konfirmasiPesanTerpilih(event)">
                        <i class="bi bi-check2-square me-1"></i> Pesan Terpilih (<span id="jmlTerpilih">0</span>)
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endif

{{-- ═══ FILTER ═══ --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold small mb-1">Cari No. PO</label>
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari no. PO..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Supplier</label>
                <select name="supplier_id" class="form-select form-select-sm">
                    <option value="">-- Semua Supplier --</option>
                    @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>
                        {{ $s->nama_supplier }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    @foreach(\App\Models\PurchaseOrder::STATUS_LABEL as $k => $v)
                    <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i></button>
                <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- ═══ TABEL PO ═══ --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table id="tabelPO" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. PO</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th class="text-center">Total Item</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Status</th>
                        <th>Dibuat Oleh</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $po)
                    <tr>
                        <td><code>{{ $po->no_po }}</code></td>
                        <td>{{ $po->tanggal_po->format('d/m/Y') }}</td>
                        <td>
                            {{ $po->supplier->nama_supplier ?? '-' }}
                            @if($po->supplier?->telepon)
                            <br><small class="text-muted"><i class="bi bi-telephone"></i> {{ $po->supplier->telepon }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $po->details->count() }} item</td>
                        <td class="text-end">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge bg-{{ $po->status_color }}">{{ $po->status_label }}</span>
                            @if($po->status == 'diterima_sebagian')
                            <br><small class="text-muted">{{ $po->total_diterima }}/{{ $po->total_dipesan }} diterima</small>
                            @endif
                        </td>
                        <td><small>{{ $po->user->name ?? '-' }}</small></td>
                        <td class="text-center">
                            @php $u = auth()->user(); @endphp
                            @can('update', $po)
                            <a href="{{ route('purchase-order.edit', $po->id) }}" class="btn btn-outline-secondary btn-sm" title="Ubah">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @endcan
                            @can('ajukan', $po)
                            <form action="{{ route('purchase-order.ajukan', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Kirim draft ini sebagai permintaan ke admin/owner?')">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" title="Kirim Permintaan">
                                    <i class="bi bi-send"></i>
                                </button>
                            </form>
                            @endcan
                            {{-- DIMATIKAN: "Buat PO ke Supplier" untuk admin — admin cukup Setujui / Tolak permintaan gudang.
                            @can('buatPo', $po)
                            <form action="{{ route('purchase-order.buat-po', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Buat PO ke supplier dari permintaan ini? Admin/owner yang memesan barang ke supplier.')">
                                @csrf
                                <button class="btn btn-success btn-sm" title="Buat PO ke Supplier">
                                    <i class="bi bi-cart-check-fill"></i>
                                </button>
                            </form>
                            @endcan --}}
                            @can('setujui', $po)
                            <form action="{{ route('purchase-order.setujui', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Setujui PO ini?')">
                                @csrf
                                <button class="btn btn-outline-success btn-sm" title="Setujui"><i class="bi bi-check-lg"></i></button>
                            </form>
                            @endcan
                            @can('tolak', $po)
                            <form action="{{ route('purchase-order.tolak', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Tolak PO ini?')">
                                @csrf
                                <button class="btn btn-outline-danger btn-sm" title="Tolak"><i class="bi bi-x-lg"></i></button>
                            </form>
                            @endcan
                            @can('kirim', $po)
                            <form action="{{ route('purchase-order.kirim', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Tandai PO dikirim oleh supplier?')">
                                @csrf
                                <button class="btn btn-outline-primary btn-sm" title="Dikirim Supplier"><i class="bi bi-truck"></i></button>
                            </form>
                            @endcan
                            @can('terima', $po)
                            <a href="{{ route('purchase-order.terima', $po->id) }}" class="btn btn-outline-success btn-sm" title="Terima Barang">
                                <i class="bi bi-box-arrow-in-down"></i>
                            </a>
                            @endcan
                            @can('batalkan', $po)
                            <form action="{{ route('purchase-order.batalkan', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Batalkan PO ini?')">
                                @csrf
                                <button class="btn btn-outline-dark btn-sm" title="Batalkan"><i class="bi bi-x-octagon"></i></button>
                            </form>
                            @endcan
                            @can('delete', $po)
                            <form action="{{ route('purchase-order.destroy', $po->id) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Hapus PO ini? Tindakan tidak dapat dibatalkan.')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" title="Hapus"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                            <a href="{{ route('purchase-order.show', $po->id) }}" class="btn btn-outline-info btn-sm" title="Detail">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-4 d-block mb-1"></i>Belum ada Purchase Order.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.po-ceklis')
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
    if ($.fn.dataTable && $('#tabelPO tbody tr').length) {
        $('#tabelPO').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.8/i18n/id.json'
            },
            dom: '<"row align-items-center mb-2"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6 text-md-end"B>>' +
                 'rt<"row mt-2"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                { extend: 'copyHtml5', text: '<i class="bi bi-clipboard"></i> Salin', className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'excelHtml5', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-sm btn-outline-success' },
                { extend: 'csvHtml5', text: '<i class="bi bi-filetype-csv"></i> CSV', className: 'btn btn-sm btn-outline-secondary' },
                { extend: 'pdfHtml5', text: '<i class="bi bi-file-earmark-pdf"></i> PDF', className: 'btn btn-sm btn-outline-danger' },
                { extend: 'print', text: '<i class="bi bi-printer"></i> Print', className: 'btn btn-sm btn-outline-primary' }
            ],
            order: [[0, 'desc']],
            columnDefs: [{ targets: -1, orderable: false }]
        });
    }
});
</script>
@endpush
