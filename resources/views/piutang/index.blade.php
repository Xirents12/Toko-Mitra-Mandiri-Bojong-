@extends('layouts.app')

@section('title', 'Piutang / Kredit')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Piutang / Kredit</h5>
    <a href="{{ route('piutang.laporan') }}" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-graph-up me-1"></i> Laporan Piutang
    </a>
</div>

{{-- Statistik --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card bg-primary text-white">
            <div class="card-body">
                <small class="opacity-75">Total Piutang Aktif</small>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card bg-success text-white">
            <div class="card-body">
                <small class="opacity-75">Total Piutang Lunas</small>
                <h4 class="fw-bold mb-0">Rp {{ number_format($totalLunas, 0, ',', '.') }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm stat-card bg-warning text-dark">
            <div class="card-body">
                <small class="opacity-75">Jumlah Piutang Aktif</small>
                <h4 class="fw-bold mb-0">{{ $piutangs->total() }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari pelanggan..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="lunas" {{ request('status') == 'lunas' ? 'selected' : '' }}>Lunas</option>
                    <option value="macet" {{ request('status') == 'macet' ? 'selected' : '' }}>Macet</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
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
                        <th class="text-end">Total Piutang</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-center">Cicilan</th>
                        <th class="text-center">Tenor</th>
                        <th class="text-center">Jatuh Tempo</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($piutangs as $p)
                    <tr>
                        <td>{{ $piutangs->firstItem() + $loop->index }}</td>
                        <td>
                            <strong>{{ $p->nama_pelanggan }}</strong>
                            @if($p->no_telepon)
                            <br><small class="text-muted">{{ $p->no_telepon }}</small>
                            @endif
                        </td>
                        <td><code>{{ $p->transaksi->no_invoice ?? '-' }}</code></td>
                        <td class="text-end">Rp {{ number_format($p->total_piutang, 0, ',', '.') }}</td>
                        <td class="text-end fw-bold {{ $p->sisa_piutang > 0 ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($p->sisa_piutang, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            {{ $p->jml_cicilan_terbayar }}/{{ $p->max_cicilan }}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $p->tenor_bulan }} bulan</span>
                        </td>
                        <td class="text-center">
                            {{ $p->tanggal_jatuh_tempo->format('d/m/Y') }}
                            @if($p->status == 'aktif' && $p->tanggal_jatuh_tempo->isPast())
                            <br><span class="badge bg-danger mt-1">Telat!</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $s = $p->status; @endphp
                            <span class="badge
                                @if($s == 'aktif') bg-warning text-dark
                                @elseif($s == 'lunas') bg-success
                                @else bg-danger
                                @endif">{{ ucfirst($s) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('piutang.show', $p->id) }}" class="btn btn-outline-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            @if($p->status == 'aktif')
                            <a href="{{ route('piutang.bayar', $p->id) }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-cash"></i>
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada data piutang.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($piutangs->hasPages())
    <div class="card-footer bg-white">
        {{ $piutangs->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection
