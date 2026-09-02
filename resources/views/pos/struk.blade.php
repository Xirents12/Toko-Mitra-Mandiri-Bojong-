@extends('layouts.app')

@section('title', 'Struk Transaksi')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="table-card p-4" id="struk">
            <div class="text-center mb-3">
                <h5 class="fw-bold">Toko Mitra Mandiri Bojong</h5>
                <small class="text-muted">Struk Transaksi</small>
                <hr>
            </div>

            <div class="row mb-2 small">
                <div class="col-6">No. Invoice</div>
                <div class="col-6 text-end"><strong><code>{{ $transaksi->no_invoice ?? '-' }}</code></strong></div>
                <div class="col-6">Tanggal</div>
                <div class="col-6 text-end">{{ $transaksi->created_at->format('d/m/Y H:i:s') }}</div>
                <div class="col-6">Kasir</div>
                <div class="col-6 text-end">{{ $transaksi->kasir_nama }}</div>
                @if($transaksi->nama_pelanggan)
                <div class="col-6">Pelanggan</div>
                <div class="col-6 text-end">{{ $transaksi->nama_pelanggan }}</div>
                @endif
                <div class="col-6">Pembayaran</div>
                <div class="col-6 text-end">
                    @if($transaksi->metode_bayar == 'kredit')
                        <span class="badge bg-warning text-dark">Kredit</span>
                    @else
                        <span class="badge bg-success">Tunai</span>
                    @endif
                </div>
            </div>

            <hr>
            <table class="table table-sm small">
                <thead>
                    <tr>
                        <th>Barang</th>
                        <th class="text-center">Qty</th>
                        <th class="text-center">Satuan</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksi->detailTransaksi as $detail)
                    <tr>
                        <td>
                            {{ $detail->barang->nama_barang }}<br>
                            <small class="text-muted">Rp {{ number_format($detail->harga_satuan,0,',','.') }}</small>
                        </td>
                        <td class="text-center">{{ $detail->jumlah }}</td>
                        <td class="text-center"><small class="text-muted">{{ $detail->barang->satuan }}</small></td>
                        <td class="text-end">Rp {{ number_format($detail->subtotal,0,',','.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
            <div class="d-flex justify-content-between fw-bold">
                <span>Total</span>
                <span>Rp {{ number_format($transaksi->total_harga,0,',','.') }}</span>
            </div>

            @if($transaksi->metode_bayar == 'tunai')
            <div class="d-flex justify-content-between">
                <span>Bayar</span>
                <span>Rp {{ number_format($transaksi->bayar,0,',','.') }}</span>
            </div>
            <div class="d-flex justify-content-between text-success fw-bold">
                <span>Kembalian</span>
                <span>Rp {{ number_format($transaksi->kembalian,0,',','.') }}</span>
            </div>
            @endif

            @if($transaksi->metode_bayar == 'kredit' && $transaksi->piutang)
            <hr>
            <div class="small">
                <p class="fw-bold mb-1 text-warning"><i class="bi bi-credit-card me-1"></i> Informasi Kredit</p>
                @if($transaksi->bayar > 0)
                <div class="d-flex justify-content-between">
                    <span>Uang Muka (DP)</span>
                    <span class="fw-bold">Rp {{ number_format($transaksi->bayar,0,',','.') }}</span>
                </div>
                @endif
                <div class="d-flex justify-content-between">
                    <span>Sisa Piutang</span>
                    <span class="fw-bold">Rp {{ number_format($transaksi->piutang->sisa_piutang,0,',','.') }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Cicilan</span>
                    <span>{{ $transaksi->piutang->max_cicilan }}x</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Tenor</span>
                    <span>{{ $transaksi->piutang->tenor_bulan }} bulan</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Jatuh Tempo</span>
                    <span>{{ $transaksi->piutang->tanggal_jatuh_tempo->format('d/m/Y') }}</span>
                </div>
            </div>
            @endif

            <hr>
            <p class="text-center small text-muted">Terima kasih atas kunjungan Anda!</p>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
            <a href="{{ route('pos.index') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-1"></i> Transaksi Baru
            </a>
            @if($transaksi->metode_bayar == 'kredit' && $transaksi->piutang)
            <a href="{{ route('piutang.bayar', $transaksi->piutang->id) }}" class="btn btn-warning">
                <i class="bi bi-cash me-1"></i> Bayar Cicilan
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
