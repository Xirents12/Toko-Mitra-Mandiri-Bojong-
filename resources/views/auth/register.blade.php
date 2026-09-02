<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — Sistem Stok Gudang Mitra Mandiri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Inter', sans-serif; }
        body {
            min-block-size: 100vh;
            background: linear-gradient(135deg, #1e293b 0%, #1e40af 100%);
            display: flex; align-items: center; justify-content: center;
            padding-block: 2rem;
        }
        .login-card {
            background: #fff;
            border-radius: 1rem;
            padding: 2.5rem;
            inline-size: 100%; max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,.25);
        }
        .login-logo {
            width: 56px; height: 56px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: .75rem;
            display: flex; align-items: center; justify-content: center;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <div class="login-logo mx-auto mb-3">
            <i class="bi bi-boxes text-white fs-3"></i>
        </div>
        <h5 class="fw-700 mb-0" style="font-weight:700">Buat Akun Baru</h5>
        <p class="text-muted small">Mitra Mandiri Bojong — Sistem Pengelolaan Stok Gudang</p>
    </div>

    @if($errors->any())
    <div class="alert alert-danger py-2 small">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-500 small">Nama Lengkap</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-person text-muted"></i>
                </span>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="form-control border-start-0 @error('name') is-invalid @enderror"
                       placeholder="Nama Anda" required autofocus>
            </div>
            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-500 small">Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-envelope text-muted"></i>
                </span>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="form-control border-start-0 @error('email') is-invalid @enderror"
                       placeholder="email@contoh.com" required>
            </div>
            @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-500 small">Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock text-muted"></i>
                </span>
                <input type="password" name="password" id="password"
                       class="form-control border-start-0 border-end-0 @error('password') is-invalid @enderror"
                       placeholder="Minimal 6 karakter" required>
                <button type="button" class="btn btn-light border"
                        onclick="togglePass('password', 'eyeIcon')">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>
            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-4">
            <label class="form-label fw-500 small">Konfirmasi Password</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0">
                    <i class="bi bi-lock-fill text-muted"></i>
                </span>
                <input type="password" name="password_confirmation" id="password_confirmation"
                       class="form-control border-start-0 border-end-0"
                       placeholder="Ulangi password" required>
                <button type="button" class="btn btn-light border"
                        onclick="togglePass('password_confirmation', 'eyeIcon2')">
                    <i class="bi bi-eye" id="eyeIcon2"></i>
                </button>
            </div>
        </div>
        <button type="submit" class="btn btn-primary w-100 py-2 fw-600">
            <i class="bi bi-person-plus me-2"></i>Daftar
        </button>
    </form>

    <div class="text-center mt-4 small">
        Sudah punya akun?
        <a href="{{ route('login') }}" class="fw-600 text-decoration-none">Masuk</a>
    </div>
</div>
<script>
function togglePass(inputId, iconId) {
    const p = document.getElementById(inputId);
    const i = document.getElementById(iconId);
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
