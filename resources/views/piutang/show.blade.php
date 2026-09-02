@extends('layouts.app')

@section('title', 'Detail Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Detail Piutang</h5>
    <div>
        @if($piutang->status == 'aktif')
        <a href="{{ route('piutang.bayar', $piutang->id) }}" class="btn btn-success btn-sm">
            <i class="bi bi-cash me-1"></i> Bayar Cicilan
        </a>
        @endif
        <a href="{{ route('piutang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Informasi Piutang</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Pelanggan</td>
                        <td class="fw-bold">{{ $piutang->nama_pelanggan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Telepon</td>
                        <td>{{ $piutang->no_telepon ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Alamat</td>
                        <td>{{ $piutang->alamat ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">No. Invoice</td>
                        <td><code>{{ $piutang->transaksi->no_invoice ?? '-' }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tanggal Transaksi</td>
                        <td>{{ $piutang->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status</td>
                        <td>
                            <span class="badge
                                @if($piutang->status == 'aktif') bg-warning text-dark
                                @elseif($piutang->status == 'lunas') bg-success
                                @else bg-danger @endif">
                                {{ ucfirst($piutang->status) }}
                            </span>
                        </td>
                    </tr>
                </table>
                <hr>
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="text-muted">Total Piutang</td>
                        <td class="fw-bold">Rp {{ number_format($piutang->total_piutang, 0, ',', '.') }}</td>
                    </tr>
                    @if(($piutang->transaksi->bayar ?? 0) > 0)
                    <tr>
                        <td class="text-muted">Uang Muka (DP)</td>
                        <td class="fw-bold text-success">Rp {{ number_format($piutang->transaksi->bayar, 0, ',', '.') }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="text-muted">Sisa Piutang</td>
                        <td class="fw-bold text-danger">Rp {{ number_format($piutang->sisa_piutang, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Besar Cicilan</td>
                        <td>Rp {{ number_format($piutang->besar_cicilan, 0, ',', '.') }}/kali</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Cicilan Terbayar</td>
                        <td>{{ $piutang->jml_cicilan_terbayar }} / {{ $piutang->max_cicilan }} kali</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Tenor</td>
                        <td>{{ $piutang->tenor_bulan }} bulan</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Jatuh Tempo</td>
                        <td>
                            {{ $piutang->tanggal_jatuh_tempo->format('d/m/Y') }}
                            @if($piutang->status == 'aktif' && $piutang->tanggal_jatuh_tempo->isPast())
                            <span class="badge bg-danger ms-1">Telat!</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Riwayat Cicilan</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Tanggal Bayar</th>
                                <th class="text-end">Jumlah</th>
                                <th class="text-center">Metode</th>
                                <th>Keterangan</th>
                                <th>Kasir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($piutang->cicilans as $cicilan)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cicilan->tanggal_bayar->format('d/m/Y') }}</td>
                                <td class="text-end fw-bold text-success">
                                    Rp {{ number_format($cicilan->jumlah, 0, ',', '.') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">{{ $cicilan->metode_bayar }}</span>
                                </td>
                                <td><small>{{ $cicilan->keterangan ?? '-' }}</small></td>
                                <td><small>{{ $cicilan->user->name ?? '-' }}</small></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                                    Belum ada cicilan.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-3">
            <div class="card-header bg-white fw-semibold">Detail Transaksi</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($piutang->transaksi->detailTransaksi as $detail)
                            <tr>
                                <td><small>{{ $detail->barang->nama_barang }}</small></td>
                                <td class="text-center"><small>{{ $detail->jumlah }}</small></td>
                                <td class="text-end"><small>Rp {{ number_format($detail->harga_satuan, 0, ',', '.') }}</small></td>
                                <td class="text-end"><small>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
