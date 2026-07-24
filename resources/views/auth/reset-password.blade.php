<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password – AbsensiKP</title>
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
            <i class="fas fa-lock fa-2x mb-2"></i>
            <h5 class="fw-bold mb-0">Buat Password Baru</h5>
        </div>
        <div class="card-body p-4">
            <form method="POST" action="{{ route('password.store') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input type="email" name="email" value="{{ old('email', $request->email) }}"
                           class="form-control @error('email') is-invalid @enderror" readonly>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Password Baru</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           placeholder="Min. 8 karakter" autofocus>
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                           placeholder="Ulangi password baru">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="fas fa-save me-2"></i>Simpan Password Baru
                </button>
            </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
