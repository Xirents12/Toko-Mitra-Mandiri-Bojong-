@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
<div class="mb-4">
    <h4 class="fw-bold">Tambah Pengguna</h4>
</div>

<div class="table-card p-4" style="max-inline-size:600px">
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('users.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="admin"  {{ old('role') == 'admin'  ? 'selected' : '' }}>Admin</option>
                <option value="kasir"  {{ old('role') == 'kasir'  ? 'selected' : '' }}>Kasir</option>
                <option value="gudang" {{ old('role') == 'gudang' ? 'selected' : '' }}>Bagian Gudang</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="is_active" class="form-select">
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
            </select>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>
@endsection