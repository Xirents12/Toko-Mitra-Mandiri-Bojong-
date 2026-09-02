@extends('layouts.app')

@section('title', 'Edit Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Edit Supplier</h5>
    <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 720px">
    <div class="card-body p-4">
        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('supplier.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Kode Supplier</label>
                    <input type="text" class="form-control" value="{{ $supplier->kode_supplier }}" disabled>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="nama_supplier" class="form-control @error('nama_supplier') is-invalid @enderror"
                        value="{{ old('nama_supplier', $supplier->nama_supplier) }}" required>
                    @error('nama_supplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Telepon</label>
                    <input type="text" name="telepon" class="form-control"
                        value="{{ old('telepon', $supplier->telepon) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $supplier->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Alamat</label>
                    <input type="text" name="alamat" class="form-control"
                        value="{{ old('alamat', $supplier->alamat) }}">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2">{{ old('keterangan', $supplier->keterangan) }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', $supplier->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active', $supplier->is_active) == 0 ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
