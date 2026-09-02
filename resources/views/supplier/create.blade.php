@extends('layouts.app')

@section('title', 'Tambah Supplier')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Tambah Supplier</h5>
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

        <form action="{{ route('supplier.store') }}" method="POST">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Nama Supplier <span class="text-danger">*</span></label>
                    <input type="text" name="nama_supplier" class="form-control @error('nama_supplier') is-invalid @enderror"
                        value="{{ old('nama_supplier') }}" placeholder="cth: CV. Sumber Bangunan" required>
                    @error('nama_supplier')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Telepon</label>
                    <input type="text" name="telepon" class="form-control" value="{{ old('telepon') }}"
                        placeholder="cth: 0812-3456-7890">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}" placeholder="supplier@email.com">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="{{ old('alamat') }}"
                        placeholder="cth: Jl. Raya Bojong No. 12">
                </div>
                <div class="col-12">
                    <label class="form-label fw-semibold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"
                        placeholder="Catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="is_active" class="form-select">
                        <option value="1" {{ old('is_active', 1) == 1 ? 'selected' : '' }}>Aktif</option>
                        <option value="0" {{ old('is_active') == 0 ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>
                <div class="col-12 d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan
                    </button>
                    <a href="{{ route('supplier.index') }}" class="btn btn-outline-secondary">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
