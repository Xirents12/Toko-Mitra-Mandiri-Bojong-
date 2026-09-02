@extends('layouts.app')

@section('title', 'Laporan Stok Overstok')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0"><i class="bi bi-exclamation-diamond me-2 text-warning"></i>Laporan Stok Overstok</h5>
    <div class="d-flex gap-1">
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-diamond fs-4"></i>
    <div>
        Barang dengan stok melebihi batas maksimum — <strong>stok berlebih</strong> yang mengikat modal.
        @if($barangs->count() > 0)
        Terdapat <strong>{{ $barangs->count() }} barang</strong> dalam kondisi overstok.
        @endif
    </div>
</div>

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
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Stok Maksimum</th>
                        <th class="text-center">Kelebihan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $i => $b)
                    @php
                        $kelebihan = $b->stok_saat_ini - $b->stok_maksimum;
                    @endphp
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $b->kode_barang }}</code></td>
                        <td>
                            <a href="{{ route('barang.show', $b) }}">{{ $b->nama_barang }}</a>
                        </td>
                        <td>{{ $b->kategori->nama_kategori ?? '-' }}</td>
                        <td>{{ $b->satuan }}</td>
                        <td class="text-center fw-bold text-warning">{{ $b->stok_saat_ini }}</td>
                        <td class="text-center text-muted">{{ $b->stok_maksimum }}</td>
                        <td class="text-center">
                            <span class="badge bg-warning text-dark">+{{ $kelebihan }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-4 d-block mb-1"></i>
                            Tidak ada barang dalam kondisi overstok.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if($barangs->count() > 0)
                <tfoot class="table-light">
                    <tr>
                        <td colspan="7" class="text-end fw-bold">Total Kelebihan Stok</td>
                        <td class="text-end fw-bold text-warning">{{ $barangs->sum(fn($b) => $b->stok_saat_ini - $b->stok_maksimum) }} unit</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection