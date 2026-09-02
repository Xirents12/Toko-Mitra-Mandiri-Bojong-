@extends('layouts.app')

@section('title', 'Laporan Mutasi Stok')
@section('page-title', 'Laporan Mutasi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Mutasi Stok</h5>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('laporan.mutasi-detail') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-clock-history me-1"></i> Riwayat Per Barang
        </a>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

{{-- Filter Tanggal --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                    value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                    value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-4 d-flex gap-1 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search me-1"></i> Tampilkan
                </button>
                <a href="{{ route('laporan.mutasi') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-success">{{ $stokMasuks->count() }}</div>
            <div class="text-muted small">Transaksi Stok Masuk</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-danger">{{ $stokKeluars->count() }}</div>
            <div class="text-muted small">Transaksi Stok Keluar</div>
        </div>
    </div>
</div>

{{-- Tabel Stok Masuk --}}
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-success text-white fw-semibold">
        <i class="bi bi-arrow-down-circle me-1"></i> Stok Masuk
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Supplier</th>
                        <th class="text-center">Jml Item</th>
                        <th class="text-end">Total Nilai</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokMasuks as $i => $sm)
                    @php
                        $totalNilai = $sm->details->sum(fn($d) => $d->jumlah * $d->harga_beli);
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $sm->no_transaksi }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($sm->tanggal_masuk)->format('d/m/Y') }}</td>
                        <td>{{ $sm->supplier->nama_supplier ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-success">{{ $sm->details->count() }} item</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                        <td>{{ $sm->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">
                            Tidak ada data stok masuk pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($stokMasuks->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="5" class="text-end">Total Nilai Masuk</th>
                        <th class="text-end text-success">
                            Rp {{ number_format($stokMasuks->sum(fn($sm) => $sm->details->sum(fn($d) => $d->jumlah * $d->harga_beli)), 0, ',', '.') }}
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

{{-- Tabel Stok Keluar --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-danger text-white fw-semibold">
        <i class="bi bi-arrow-up-circle me-1"></i> Stok Keluar
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>No. Transaksi</th>
                        <th>Tanggal</th>
                        <th>Penerima</th>
                        <th>Jenis</th>
                        <th class="text-center">Jml Item</th>
                        <th class="text-end">Total Nilai</th>
                        <th>Dicatat Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stokKeluars as $i => $sk)
                    @php
                        $totalNilai = $sk->details->sum(fn($d) => $d->jumlah * ($d->harga_jual ?? 0));
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $sk->no_transaksi }}</code></td>
                        <td>{{ \Carbon\Carbon::parse($sk->tanggal_keluar)->format('d/m/Y') }}</td>
                        <td>{{ $sk->nama_pelanggan ?? '-' }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($sk->jenis_keluar ?? '-') }}</span></td>
                        <td class="text-center">
                            <span class="badge bg-danger">{{ $sk->details->count() }} item</span>
                        </td>
                        <td class="text-end">Rp {{ number_format($totalNilai, 0, ',', '.') }}</td>
                        <td>{{ $sk->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">
                            Tidak ada data stok keluar pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($stokKeluars->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <th colspan="6" class="text-end">Total Nilai Keluar</th>
                        <th class="text-end text-danger">
                            Rp {{ number_format($stokKeluars->sum(fn($sk) => $sk->details->sum(fn($d) => $d->jumlah * ($d->harga_jual ?? 0))), 0, ',', '.') }}
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection