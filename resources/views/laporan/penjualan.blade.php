@extends('layouts.app')

@section('title', 'Laporan Penjualan')
@section('page-title', 'Laporan Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Penjualan</h5>
    <div class="d-flex gap-1">
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Kasir</label>
                <select name="kasir" class="form-select form-select-sm">
                    <option value="">-- Semua Kasir --</option>
                    @foreach($kasirs as $k)
                        <option value="{{ $k }}" {{ request('kasir') == $k ? 'selected' : '' }}>{{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Metode Bayar</label>
                <select name="metode" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    @foreach($metodes as $val => $label)
                        <option value="{{ $val }}" {{ request('metode') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex gap-1 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Tampilkan</button>
                <a href="{{ route('laporan.penjualan') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-primary">{{ $totalTransaksi }}</div>
            <div class="text-muted small">Total Transaksi</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-info">{{ $totalItem }}</div>
            <div class="text-muted small">Total Item Terjual</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-success">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
            <div class="text-muted small">Total Penjualan</div>
        </div>
    </div>
</div>

{{-- Tabel Transaksi --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-1 text-primary"></i> Detail Transaksi Penjualan</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Tanggal</th>
                        <th>Kasir</th>
                        <th>Pelanggan</th>
                        <th>Metode</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Penjualan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <code>{{ $t->no_invoice }}</code>
                            <a href="{{ route('laporan.cetak-nota', $t->id) }}" target="_blank" class="btn btn-outline-primary btn-sm py-0 ms-1" title="Cetak Nota Ini"><i class="bi bi-printer"></i></a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $t->nama_kasir ?? '-' }}</td>
                        <td>{{ $t->nama_pelanggan ?? '-' }}</td>
                        <td>
                            <span class="badge {{ $t->metode_bayar == 'kredit' ? 'bg-warning text-dark' : 'bg-success' }}">
                                {{ ucfirst($t->metode_bayar) }}
                            </span>
                        </td>
                        <td class="text-center"><span class="badge bg-primary">{{ $t->detailTransaksi->count() }} item</span></td>
                        <td class="text-end fw-semibold">Rp {{ number_format($t->total_harga, 0, ',', '.') }}</td>
                    </tr>
                    @foreach($t->detailTransaksi as $d)
                    <tr class="table-light text-muted small">
                        <td></td>
                        <td colspan="4" class="ps-4">
                            <i class="bi bi-arrow-return-right me-1"></i>
                            {{ $d->barang->nama_barang ?? '-' }}
                            <code class="ms-1">{{ $d->barang->kode_barang ?? '' }}</code>
                        </td>
                        <td class="text-center">{{ $d->jumlah }} {{ $d->barang->satuan ?? '' }} × Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $d->subtotal ? 'Rp ' . number_format($d->subtotal, 0, ',', '.') : '-' }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada transaksi penjualan pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($transaksis->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="7" class="text-end">Total</th>
                        <th class="text-end text-success">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Rekap per Barang & Rekap per Kasir DI-NONAKTIFKAN (diminta user) --}}

@endsection
