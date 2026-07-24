<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password – AbsensiKP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background:linear-gradient(135deg,#1e3a5f,#0d2137); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .auth-card { border:none; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:420px; overflow:hidden; }
        .auth-header { background:linear-gradient(135deg,#1e3a5f,#2d6a9f); padding:1.75rem; text-align:center; color:#fff; }
        .btn-primary { background:linear-gradient(135deg,#1e3a5f,#2d6a9f); border:none; }
    </style>
</head>
<body>
    <div class="auth-card card">
        <div class="auth-header">
            <i class="fas fa-key fa-2x mb-2"></i>
            <h5 class="fw-bold mb-0">Lupa Password</h5>
            <p class="mb-0 small opacity-75">Masukkan email untuk reset password</p>
        </div>
        <div class="card-body p-4">
            @if(session('status'))
                <div class="alert alert-success py-2 small">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="mb-4">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="email@contoh.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-paper-plane me-2"></i>Kirim Link Reset
                </button>
            </form>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}" class="text-primary small text-decoration-none">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Login
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
