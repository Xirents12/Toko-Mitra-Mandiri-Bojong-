@extends('layouts.app')

@section('title', 'Laporan Stok Barang')
@section('page-title', 'Laporan Stok Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Stok Barang</h5>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control form-control-sm"
                    placeholder="Cari nama / kode barang..."
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="kategori_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($kategoris as $kat)
                        <option value="{{ $kat->id }}" {{ request('kategori_id') == $kat->id ? 'selected' : '' }}>
                            {{ $kat->nama_kategori }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status_stok" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="normal"    {{ request('status_stok') == 'normal'    ? 'selected' : '' }}>Normal</option>
                    <option value="menipis"   {{ request('status_stok') == 'menipis'   ? 'selected' : '' }}>Menipis</option>
                    <option value="habis"     {{ request('status_stok') == 'habis'     ? 'selected' : '' }}>Habis</option>
                    <option value="overstock" {{ request('status_stok') == 'overstock' ? 'selected' : '' }}>Overstock</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('laporan.stok') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-primary">{{ $barangs->count() }}</div>
            <div class="text-muted small">Total Barang</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-success">{{ $barangs->where('stok_saat_ini', '>', 0)->count() }}</div>
            <div class="text-muted small">Stok Tersedia</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-warning">
                {{ $barangs->filter(fn($b) => $b->stok_saat_ini > 0 && $b->stok_saat_ini <= $b->stok_minimum)->count() }}
            </div>
            <div class="text-muted small">Stok Menipis</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-danger">{{ $barangs->where('stok_saat_ini', '<=', 0)->count() }}</div>
            <div class="text-muted small">Stok Habis</div>
        </div>
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
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th>Satuan</th>
                        <th class="text-center">Stok</th>
                        <th class="text-center">Min</th>
                        <th class="text-center">Maks</th>
                        <th class="text-end">Harga Jual</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $i => $barang)
                    @php $status = $barang->status_stok; @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $barang->kode_barang }}</code></td>
                        <td>{{ $barang->nama_barang }}</td>
                        <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $barang->satuan }}</td>
                        <td class="text-center fw-bold
                            @if($status == 'habis') text-danger
                            @elseif($status == 'menipis') text-warning
                            @else text-success @endif">
                            {{ $barang->stok_saat_ini }}
                        </td>
                        <td class="text-center text-muted">{{ $barang->stok_minimum }}</td>
                        <td class="text-center text-muted">{{ $barang->stok_maksimum }}</td>
                        <td class="text-end">Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                        <td class="text-center">
                            <span class="badge
                                @if($status == 'normal') bg-success
                                @elseif($status == 'menipis') bg-warning text-dark
                                @elseif($status == 'habis') bg-danger
                                @else bg-info text-dark @endif">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada data barang.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection