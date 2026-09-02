@extends('layouts.app')

@section('title', 'Laporan Per Nota')
@section('page-title', 'Laporan Per Nota')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Per Nota</h5>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

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
                <a href="{{ route('laporan.per-nota') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i> Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-primary">{{ $totalNota }}</div>
            <div class="text-muted small">Total Nota</div>
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
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-warning">Rp {{ number_format($totalLaba, 0, ',', '.') }}</div>
            <div class="text-muted small">Total Laba</div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-semibold"><i class="bi bi-receipt me-1 text-primary"></i> Klasifikasi per Nota</div>
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
                        <th>Klasifikasi</th>
                        <th class="text-center">Ukuran</th>
                        <th class="text-center">Item</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Laba</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $i => $t)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>
                            <code>{{ $t->no_invoice }}</code>
                            <a href="{{ route('laporan.cetak-nota', $t->id) }}" target="_blank" class="btn btn-outline-primary btn-sm py-0 ms-1" title="Cetak Nota Ini"><i class="bi bi-printer"></i></a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($t->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $t->kasir }}</td>
                        <td>{{ $t->pelanggan }}</td>
                        <td>
                            <span class="badge {{ $klasifikasiMap[$t->klasifikasi]['badge'] }}">{{ $t->klasifikasi }}</span>
                            @if($t->retur)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Retur</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $ukuranMap[$t->ukuran] }}">{{ $t->ukuran }}</span>
                        </td>
                        <td class="text-center"><span class="badge bg-primary">{{ $t->item_count }} item</span></td>
                        <td class="text-end fw-semibold">Rp {{ number_format($t->total, 0, ',', '.') }}</td>
                        <td class="text-end fw-semibold">Rp {{ number_format($t->laba, 0, ',', '.') }}</td>
                    </tr>
                    @foreach($t->detail as $d)
                    <tr class="table-light text-muted small">
                        <td></td>
                        <td colspan="5" class="ps-4">
                            <i class="bi bi-arrow-return-right me-1"></i>
                            {{ $d->barang->nama_barang ?? '-' }}
                            <code class="ms-1">{{ $d->barang->kode_barang ?? '' }}</code>
                        </td>
                        <td class="text-center">{{ $d->jumlah }} {{ $d->barang->satuan ?? '' }} × Rp {{ number_format($d->harga_satuan, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $d->subtotal ? 'Rp ' . number_format($d->subtotal, 0, ',', '.') : '-' }}</td>
                        <td class="text-center">{{ $d->harga_beli ? 'Rp ' . number_format(($d->subtotal - ($d->jumlah * $d->harga_beli)), 0, ',', '.') : '-' }}</td>
                        <td></td>
                    </tr>
                    @endforeach
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada transaksi pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($items->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="8" class="text-end">Total</th>
                        <th class="text-end text-success">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</th>
                        <th class="text-end text-warning">Rp {{ number_format($totalLaba, 0, ',', '.') }}</th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Rekap per Klasifikasi Pembayaran --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-tags me-1 text-primary"></i> Rekap per Klasifikasi Pembayaran</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Klasifikasi</th>
                                <th class="text-center">Nota</th>
                                <th class="text-end">Penjualan</th>
                                <th class="text-end">Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapKlasifikasi as $r)
                            <tr>
                                <td><span class="badge {{ $klasifikasiMap[$r['klasifikasi']]['badge'] }}">{{ $r['klasifikasi'] }}</span></td>
                                <td class="text-center">{{ $r['nota'] }}</td>
                                <td class="text-end">Rp {{ number_format($r['penjualan'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($r['laba'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Rekap per Ukuran Transaksi --}}
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-bar-chart me-1 text-primary"></i> Rekap per Ukuran Transaksi</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ukuran</th>
                                <th class="text-center">Nota</th>
                                <th class="text-end">Penjualan</th>
                                <th class="text-end">Laba</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rekapUkuran as $r)
                            <tr>
                                <td><span class="badge {{ $ukuranMap[$r['ukuran']] }}">{{ $r['ukuran'] }}</span></td>
                                <td class="text-center">{{ $r['nota'] }}</td>
                                <td class="text-end">Rp {{ number_format($r['penjualan'], 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($r['laba'], 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
