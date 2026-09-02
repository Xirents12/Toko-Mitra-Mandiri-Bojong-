@extends('layouts.app')

@section('title', 'Laporan Stok Kritis')
@section('page-title', 'Laporan Stok Kritis')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <h5 class="fw-bold mb-0">Laporan Stok Kritis</h5>
    <div class="d-flex gap-1 flex-wrap">
        @if(auth()->user()->isGudang() && $barangs->isNotEmpty())
        <form action="{{ route('purchase-order.auto-kritis') }}" method="POST" class="d-inline-block"
              onsubmit="return confirm('Buat permintaan dari SEMUA barang stok kritis? Permintaan dibuat per supplier dan dikirim ke admin/owner untuk disetujui.')">
            @csrf
            <button class="btn btn-danger btn-sm">
                <i class="bi bi-lightning-charge-fill me-1"></i> Pesan Barang Kritis (Semua)
            </button>
        </form>
        <form action="{{ route('purchase-order.auto-kritis') }}" method="POST" class="d-inline-block" id="formPesanTerpilih">
            @csrf
            <input type="hidden" name="barang_ids" id="barangIdsTerpilih" value="">
            <button type="submit" id="btnPesanTerpilih" class="btn btn-outline-danger btn-sm" disabled
                    onclick="return konfirmasiPesanTerpilih(event)">
                <i class="bi bi-check2-square me-1"></i> Pesan Terpilih (<span id="jmlTerpilih">0</span>)
            </button>
        </form>
        @endif
        <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
        <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

{{-- Ringkasan --}}
<div class="row g-3 mb-3 no-print">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-danger">{{ $barangs->count() }}</div>
            <div class="text-muted small">Barang Kritis</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-dark">{{ $barangs->where('stok_saat_ini', '<=', 0)->count() }}</div>
            <div class="text-muted small">Stok Habis</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fs-4 fw-bold text-warning">
                {{ $barangs->filter(fn($b) => $b->stok_saat_ini > 0 && $b->stok_saat_ini <= $b->stok_minimum)->count() }}
            </div>
            <div class="text-muted small">Menipis (di bawah minimum)</div>
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
                        @if(auth()->user()->isGudang())
                        <th class="text-center" style="width:42px">
                            <input type="checkbox" class="form-check-input" id="pilihSemua" title="Pilih semua barang">
                        </th>
                        @endif
                        <th>#</th>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Kategori</th>
                        <th class="text-center">Stok Saat Ini</th>
                        <th class="text-center">Stok Minimum</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($barangs as $i => $barang)
                    <tr>
                        @if(auth()->user()->isGudang())
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input check-item" value="{{ $barang->id }}"
                                   data-nama="{{ $barang->nama_barang }}">
                        </td>
                        @endif
                        <td>{{ $i + 1 }}</td>
                        <td><code>{{ $barang->kode_barang }}</code></td>
                        <td>{{ $barang->nama_barang }}</td>
                        <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $barang->stok_saat_ini <= 0 ? 'bg-danger' : 'bg-warning text-dark' }}">
                                {{ $barang->stok_saat_ini }} {{ $barang->satuan }}
                            </span>
                        </td>
                        <td class="text-center text-muted">{{ $barang->stok_minimum }}</td>
                        <td class="text-center">
                            @if($barang->stok_saat_ini <= 0)
                                <span class="badge bg-danger">Habis</span>
                            @else
                                <span class="badge bg-warning text-dark">Menipis</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-check-circle fs-4 d-block mb-1 text-success"></i>
                            Tidak ada stok kritis. Semua barang aman.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@include('partials.po-ceklis')
@endpush
