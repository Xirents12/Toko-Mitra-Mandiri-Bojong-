@extends('layouts.app')

@section('title', 'Profil Saya')
@section('page-title', 'Profil Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="fw-bold mb-0">Profil Saya</h5>
    <a href="{{ route('landing') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="row g-3">
    {{-- Info Akun --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <div class="mx-auto mb-3 d-flex align-items-center justify-content-center"
                    style="width:84px;height:84px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-2));color:#fff;font-size:1.9rem;font-weight:800">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <h5 class="fw-bold mb-1">{{ auth()->user()->name }}</h5>
                <div class="text-muted small mb-3">{{ auth()->user()->email }}</div>
                <span class="badge bg-primary-subtle text-primary border">{{ auth()->user()->role_label }}</span>

                <hr>
                <table class="table table-sm table-borderless text-start small mb-0">
                    <tr>
                        <td class="text-muted">Nama</td>
                        <td class="fw-semibold text-end">{{ auth()->user()->name }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Email</td>
                        <td class="fw-semibold text-end">{{ auth()->user()->email }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Peran</td>
                        <td class="fw-semibold text-end">{{ auth()->user()->role_label }}</td>
                    </tr>
                    <tr>
                        <td class="text-muted">Terdaftar</td>
                        <td class="fw-semibold text-end">{{ auth()->user()->created_at->translatedFormat('d F Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    {{-- Ubah Password --}}
    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold"><i class="bi bi-shield-lock me-1 text-primary"></i> Ubah Password</div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Lama</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password Baru</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimal 8 karakter.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
