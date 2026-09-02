@extends('layouts.app')

@section('title', 'Laporan Piutang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Piutang</h5>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('piutang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Data Piutang
        </a>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body">
                <small class="opacity-75">Total Piutang</small>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body">
                <small class="opacity-75">Total Terkumpul</small>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalTerkumpul, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm bg-danger text-white">
            <div class="card-body">
                <small class="opacity-75">Sisa Piutang</small>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalSisa, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <label class="form-label small">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm"
                    value="{{ request('tanggal_awal') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm"
                    value="{{ request('tanggal_akhir') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Pelanggan</th>
                        <th>Invoice</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-end">Terkumpul</th>
                        <th class="text-center">Cicilan</th>
                        <th class="text-center">Tenor</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutangs as $p)
                    @php $terkumpul = $p->total_piutang - $p->sisa_piutang; @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $p->nama_pelanggan }}</td>
                        <td><code>{{ $p->transaksi->no_invoice ?? '-' }}</code></td>
                        <td class="text-end">Rp {{ number_format($p->total_piutang, 0, ',', '.') }}</td>
                        <td class="text-end {{ $p->sisa_piutang > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($p->sisa_piutang, 0, ',', '.') }}
                        </td>
                        <td class="text-end text-success">Rp {{ number_format($terkumpul, 0, ',', '.') }}</td>
                        <td class="text-center">{{ $p->jml_cicilan_terbayar }}/{{ $p->max_cicilan }}</td>
                        <td class="text-center">{{ $p->tenor_bulan }} bulan</td>
                        <td class="text-center">
                            <span class="badge
                                @if($p->status == 'aktif') bg-warning text-dark
                                @elseif($p->status == 'lunas') bg-success
                                @else bg-danger
                                @endif">{{ ucfirst($p->status) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Tidak ada data piutang.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
