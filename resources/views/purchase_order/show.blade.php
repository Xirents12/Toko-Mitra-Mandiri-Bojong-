@extends('layouts.app')

@section('title', 'Detail Purchase Order')

@section('content')
@php $u = auth()->user(); @endphp
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Detail Purchase Order</h5>
    <div class="d-flex gap-1 flex-wrap">
        <a href="{{ route('purchase-order.cetak', $purchaseOrder->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak PO
        </a>
        @can('update', $purchaseOrder)
        <a href="{{ route('purchase-order.edit', $purchaseOrder->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-pencil me-1"></i> Ubah
        </a>
        @endcan
        @can('ajukan', $purchaseOrder)
        <form action="{{ route('purchase-order.ajukan', $purchaseOrder->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Ajukan PO ke Pemilik untuk persetujuan?')">
            @csrf
            <button class="btn btn-primary btn-sm"><i class="bi bi-send me-1"></i> Ajukan ke Pemilik</button>
        </form>
        @endcan
        @can('setujui', $purchaseOrder)
        <form action="{{ route('purchase-order.setujui', $purchaseOrder->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Setujui PO ini?')">
            @csrf
            <button class="btn btn-success btn-sm"><i class="bi bi-check-lg me-1"></i> Setujui</button>
        </form>
        @endcan
        @can('tolak', $purchaseOrder)
        <form action="{{ route('purchase-order.tolak', $purchaseOrder->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Tolak PO ini?')">
            @csrf
            <button class="btn btn-danger btn-sm"><i class="bi bi-x-lg me-1"></i> Tolak</button>
        </form>
        @endcan
        @can('kirim', $purchaseOrder)
        <form action="{{ route('purchase-order.kirim', $purchaseOrder->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Tandai PO dikirim oleh supplier?')">
            @csrf
            <button class="btn btn-outline-primary btn-sm"><i class="bi bi-truck me-1"></i> Dikirim Supplier</button>
        </form>
        @endcan
        @can('terima', $purchaseOrder)
        <a href="{{ route('purchase-order.terima', $purchaseOrder->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-box-arrow-in-down me-1"></i> Terima Barang
        </a>
        @endcan
        @can('batalkan', $purchaseOrder)
        <form action="{{ route('purchase-order.batalkan', $purchaseOrder->id) }}" method="POST" class="d-inline"
              onsubmit="return confirm('Batalkan PO ini?')">
            @csrf
            <button class="btn btn-outline-dark btn-sm"><i class="bi bi-x-octagon me-1"></i> Batalkan</button>
        </form>
        @endcan
        <a href="{{ route('purchase-order.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-box-seam me-1 text-primary"></i>Item Barang</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Barang</th>
                                <th class="text-center">Qty Pesan</th>
                                <th class="text-center">Qty Diterima</th>
                                <th class="text-center">Selisih</th>
                                <th class="text-end">Harga Beli</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseOrder->details as $detail)
                            @php $selisih = $detail->jumlah - $detail->qty_diterima; @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $detail->barang->nama_barang }}</span><br>
                                    <small class="text-muted">{{ $detail->barang->kode_barang }} · {{ $detail->barang->satuan }}</small>
                                </td>
                                <td class="text-center">{{ $detail->jumlah }}</td>
                                <td class="text-center">
                                    @if($detail->qty_diterima > 0)
                                    <span class="badge bg-success">{{ $detail->qty_diterima }}</span>
                                    @else
                                    <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($selisih > 0)
                                    <span class="badge bg-warning text-dark">{{ $selisih }}</span>
                                    @else
                                    <span class="badge bg-success">0</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}
                                    @if($detail->qty_diterima > 0)
                                    <br><span class="badge bg-success-subtle text-success-emphasis" title="Harga asli sesuai nota saat barang diterima">Aktual</span>
                                    @else
                                    <br><span class="badge bg-secondary-subtle text-secondary-emphasis" title="Belum diterima — harga masih estimasi">Estimasi</span>
                                    @endif
                                </td>
                                <td class="text-end">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="6" class="text-end">Total</td>
                                <td class="text-end">Rp {{ number_format($purchaseOrder->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-info-circle me-1 text-primary"></i>Informasi PO</div>
            <div class="card-body">
                <table class="table table-borderless mb-0 small">
                    <tr>
                        <td class="text-muted">No. PO</td>
                        <td class="text-end"><code>{{ $purchaseOrder->no_po }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td class="text-end">{{ $purchaseOrder->tanggal_po->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Estimasi Datang</td>
                        <td class="text-end">{{ $purchaseOrder->estimasi_datang?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Supplier</td>
                        <td class="text-end">{{ $purchaseOrder->supplier->nama_supplier ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Telepon Supplier</td>
                        <td class="text-end">
                            @if($purchaseOrder->supplier?->telepon)
                            <a href="tel:{{ $purchaseOrder->supplier->telepon }}" class="fw-semibold text-success">
                                <i class="bi bi-telephone me-1"></i>{{ $purchaseOrder->supplier->telepon }}
                            </a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat Supplier</td>
                        <td class="text-end">{{ $purchaseOrder->supplier->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td class="text-end">
                            <span class="badge bg-{{ $purchaseOrder->status_color }}">{{ $purchaseOrder->status_label }}</span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Diterima</td>
                        <td class="text-end">{{ $purchaseOrder->total_diterima }} / {{ $purchaseOrder->total_dipesan }} item</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dibuat Oleh</td>
                        <td class="text-end">{{ $purchaseOrder->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Catatan</td>
                        <td class="text-end">{{ $purchaseOrder->catatan ?? '-' }}</td>
                    </tr>
                </table>

                @if($purchaseOrder->supplier?->telepon || $purchaseOrder->supplier?->email)
                <div class="alert alert-success py-2 small mb-0 mt-2">
                    <i class="bi bi-shop me-1"></i><strong>Kontak untuk pemesanan:</strong><br>
                    {{ $purchaseOrder->supplier->nama_supplier }}
                    @if($purchaseOrder->supplier->telepon) ·
                    <a href="tel:{{ $purchaseOrder->supplier->telepon }}">{{ $purchaseOrder->supplier->telepon }}</a>
                    @endif
                    @if($purchaseOrder->supplier->email) · {{ $purchaseOrder->supplier->email }} @endif
                </div>
                @endif

                @if($purchaseOrder->status == 'selesai')
                <div class="alert alert-success py-2 small mb-0 mt-2">
                    <i class="bi bi-check-circle-fill me-1"></i>PO selesai — seluruh barang sudah diterima.
                </div>
                @endif
                @if($purchaseOrder->status == 'ditolak')
                <div class="alert alert-danger py-2 small mb-0 mt-2">
                    <i class="bi bi-x-circle-fill me-1"></i>PO ditolak oleh Pemilik.
                </div>
                @endif
                @if($purchaseOrder->status == 'dibatalkan')
                <div class="alert alert-dark py-2 small mb-0 mt-2">
                    <i class="bi bi-x-octagon-fill me-1"></i>PO dibatalkan.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
