<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – AbsensiKP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .auth-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 420px;
            overflow: hidden;
        }
        .auth-header {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            padding: 2rem;
            text-align: center;
            color: #fff;
        }
        .auth-header .icon { font-size: 2.5rem; margin-bottom: 0.5rem; }
        .auth-body { padding: 2rem; }
        .btn-primary { background: linear-gradient(135deg,#1e3a5f,#2d6a9f); border: none; }
        .btn-primary:hover { background: linear-gradient(135deg,#2d6a9f,#1e3a5f); }
        .divider { text-align:center; color:#aaa; margin: 1rem 0; position:relative; }
        .divider::before, .divider::after {
            content:''; position:absolute; top:50%; width:42%; height:1px; background:#e0e0e0;
        }
        .divider::before { left:0; } .divider::after { right:0; }
    </style>
</head>
<body>
    <div class="auth-card card">
        <div class="auth-header">
            <div class="icon"><i class="fas fa-fingerprint"></i></div>
            <h4 class="fw-bold mb-0">AbsensiKP</h4>
            <p class="mb-0 small opacity-75">Sistem Absensi GPS</p>
        </div>

        <div class="auth-body">
            @if(session('status'))
                <div class="alert alert-success py-2 small">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="email" name="email" value="{{ old('email') }}" autofocus
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="email@contoh.com">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between">
                        <label class="form-label fw-semibold">Password</label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small text-primary text-decoration-none">
                                Lupa password?
                            </a>
                        @endif
                    </div>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="••••••••">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-sign-in-alt me-2"></i>Masuk
                </button>
            </form>

            <div class="divider mt-4">atau</div>

            <div class="text-center">
                <p class="text-muted small mb-0">Belum punya akun?
                    <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Daftar di sini</a>
                </p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
