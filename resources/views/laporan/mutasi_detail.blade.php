@extends('layouts.app')

@section('title', 'Riwayat Mutasi Stok')
@section('page-title', 'Riwayat Mutasi Stok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Riwayat Mutasi Stok (Log Otomatis)</h5>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('laporan.mutasi') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Mutasi Stok
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3 no-print">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small mb-1">Tanggal Awal</label>
                <input type="date" name="tanggal_awal" class="form-control form-control-sm" value="{{ $tanggalAwal }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-1">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="{{ $tanggalAkhir }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Tipe</label>
                <select name="tipe" class="form-select form-select-sm">
                    <option value="">-- Semua --</option>
                    <option value="masuk"  {{ request('tipe') == 'masuk'  ? 'selected' : '' }}>Masuk</option>
                    <option value="keluar" {{ request('tipe') == 'keluar' ? 'selected' : '' }}>Keluar</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small mb-1">Cari Barang</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Nama barang..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex gap-1 align-items-end">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search me-1"></i> Filter</button>
                <a href="{{ route('laporan.mutasi-detail') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x"></i></a>
            </div>
        </form>
    </div>
</div>

{{-- Tabel --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Waktu</th>
                        <th>Barang</th>
                        <th class="text-center">Tipe</th>
                        <th class="text-center">Jumlah</th>
                        <th>Keterangan</th>
                        <th>Oleh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutasis as $i => $m)
                    <tr>
                        <td>{{ $mutasis->firstItem() + $i }}</td>
                        <td class="small text-muted">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            {{ $m->barang->nama_barang ?? '-' }}
                            <code class="small ms-1">{{ $m->barang->kode_barang ?? '' }}</code>
                        </td>
                        <td class="text-center">
                            @if($m->tipe == 'masuk')
                                <span class="badge bg-success"><i class="bi bi-arrow-down-circle me-1"></i>Masuk</span>
                            @else
                                <span class="badge bg-danger"><i class="bi bi-arrow-up-circle me-1"></i>Keluar</span>
                            @endif
                        </td>
                        <td class="text-center fw-bold">{{ $m->jumlah }} {{ $m->barang->satuan ?? '' }}</td>
                        <td class="small">{{ $m->keterangan }}</td>
                        <td class="small text-muted">{{ $m->user->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Belum ada mutasi stok pada periode ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($mutasis->hasPages())
    <div class="card-footer bg-white">{{ $mutasis->links() }}</div>
    @endif
</div>
@endsection
