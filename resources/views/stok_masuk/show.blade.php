@extends('layouts.app')

@section('title', 'Detail Stok Masuk')
@section('page-title', 'Detail Stok Masuk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Detail Stok Masuk</h5>
    <div class="d-flex gap-2">
        <a href="{{ route('stok-masuk.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <form action="{{ route('stok-masuk.destroy', $stokMasuk->id) }}" method="POST"
              onsubmit="return confirm('Hapus data ini? Stok barang akan dikembalikan.')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm">
                <i class="bi bi-trash me-1"></i> Hapus
            </button>
        </form>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Informasi Transaksi</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted" width="140">No. Transaksi</td>
                        <td><code>{{ $stokMasuk->no_transaksi }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal</td>
                        <td>{{ \Carbon\Carbon::parse($stokMasuk->tanggal_masuk)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Supplier</td>
                        <td>{{ $stokMasuk->supplier->nama_supplier ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Nota</td>
                        <td>{{ $stokMasuk->no_nota_supplier ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Keterangan</td>
                        <td>{{ $stokMasuk->keterangan ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Dicatat Oleh</td>
                        <td>{{ $stokMasuk->user->name ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Detail Barang</div>
            <div class="card-body p-0">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Barang</th>
                            <th class="text-center">Jumlah</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @foreach($stokMasuk->details as $i => $detail)
                        @php $subtotal = $detail->jumlah * $detail->harga_beli; $total += $subtotal; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                {{ $detail->barang->nama_barang ?? '-' }}
                                <small class="text-muted d-block">{{ $detail->barang->kode_barang ?? '' }}</small>
                            </td>
                            <td class="text-center">{{ $detail->jumlah }} {{ $detail->barang->satuan ?? '' }}</td>
                            <td class="text-end">Rp {{ number_format($detail->harga_beli, 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <th colspan="4" class="text-end">Total Nilai</th>
                            <th class="text-end text-success">Rp {{ number_format($total, 0, ',', '.') }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection