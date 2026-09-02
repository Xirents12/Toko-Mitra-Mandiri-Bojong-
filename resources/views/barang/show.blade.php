@extends('layouts.app')

@section('title', 'Detail Barang')
@section('page-title', 'Detail Barang')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Detail Barang</h5>
    <div class="d-flex gap-2">
        @if(auth()->user()->isGudang())
        <a href="{{ route('barang.edit', $barang->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i> Edit
        </a>
        @endif
        <a href="{{ route('barang.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Informasi Barang</div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td width="180" class="text-muted">Kode Barang</td>
                        <td><code>{{ $barang->kode_barang }}</code></td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Barang</td>
                        <td>{{ $barang->nama_barang }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Kategori</td>
                        <td>{{ $barang->kategori->nama_kategori ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Satuan</td>
                        <td>{{ $barang->satuan }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Harga Beli</td>
                        <td>Rp {{ number_format($barang->harga_beli, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Harga Jual</td>
                        <td>Rp {{ number_format($barang->harga_jual, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Deskripsi</td>
                        <td>{{ $barang->deskripsi ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">Info Stok</div>
            <div class="card-body">
                <div class="text-center mb-3">
                    @php $status = $barang->status_stok; @endphp
                    <span class="badge fs-6
                        @if($status == 'normal') bg-success
                        @elseif($status == 'menipis') bg-warning text-dark
                        @elseif($status == 'habis') bg-danger
                        @else bg-info text-dark
                        @endif">
                        {{ ucfirst($status) }}
                    </span>
                </div>
                <table class="table table-borderless mb-0 text-center">
                    <tr>
                        <td class="text-muted small">Stok Saat Ini</td>
                        <td class="text-muted small">Minimum</td>
                        <td class="text-muted small">Maksimum</td>
                    </tr>
                    <tr>
                        <td class="fw-bold fs-5">{{ $barang->stok_saat_ini }}</td>
                        <td class="fw-bold fs-5 text-warning">{{ $barang->stok_minimum }}</td>
                        <td class="fw-bold fs-5 text-info">{{ $barang->stok_maksimum }}</td>
                    </tr>
                </table>
            </div>
        </div>

        @if(auth()->user()->isGudang())
        <div class="card border-0 shadow-sm mt-3">
            <div class="card-body">
                <form action="{{ route('barang.destroy', $barang->id) }}" method="POST"
                      onsubmit="return confirm('Yakin hapus barang ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger w-100" type="submit">
                        <i class="bi bi-trash me-1"></i> Hapus Barang
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection