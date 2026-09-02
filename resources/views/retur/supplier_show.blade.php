@extends('layouts.app')

@section('title', 'Detail Retur ke Supplier')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <h5 class="fw-bold mb-0">Detail Retur ke Supplier</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('retur.supplier-riwayat') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat
        </a>
        <form action="{{ route('retur.supplier-destroy', $stokKeluar->id) }}" method="POST"
              onsubmit="return confirm('Batalkan retur ini? Stok barang akan dikembalikan.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm">
                <i class="bi bi-trash me-1"></i> Batalkan Retur
            </button>
        </form>
    </div>
</div>

@php
    $totalNilai = $stokKeluar->total;
    $totalQty   = $stokKeluar->details->sum('jumlah');
@endphp

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Informasi Transaksi</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="140">No. Transaksi</td>
                        <td><code>{{ $stokKeluar->no_transaksi }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($stokKeluar->tanggal_keluar)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Supplier</td>
                        <td>
                            <span class="badge bg-primary">{{ $stokKeluar->supplier->nama_supplier ?? '-' }}</span>
                            @if($stokKeluar->supplier?->alamat)
                            <div class="small text-muted mt-1">{{ $stokKeluar->supplier->alamat }}</div>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td>{{ $stokKeluar->keterangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dicatat Oleh</td>
                        <td>{{ $stokKeluar->user->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Qty</td>
                        <td class="fw-semibold">{{ $totalQty }} unit</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Total Nilai</td>
                        <td class="fw-bold text-danger">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Detail Barang Diretur</div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barang</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Subtotal</th>
                            <th>Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stokKeluar->details as $i => $detail)
                        @php
                            $subtotal = $detail->jumlah * ($detail->harga_beli ?: $detail->harga_jual);
                            $alasan = preg_match('/Alasan:\s*(.+?)\s*$/', (string) $detail->keterangan, $m) ? $m[1] : ($detail->keterangan ?? '-');
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $detail->barang->nama_barang ?? '-' }}
                                <small class="text-muted d-block">{{ $detail->barang->kode_barang ?? '' }}</small>
                            </td>
                            <td class="text-center">{{ $detail->jumlah }} {{ $detail->barang->satuan ?? '' }}</td>
                            <td class="text-end">Rp {{ number_format($detail->harga_beli ?: $detail->harga_jual, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-warning-subtle text-warning-emphasis">{{ $alasan }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total Nilai</th>
                            <th class="text-end text-danger">Rp {{ number_format($totalNilai, 0, ',', '.') }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
