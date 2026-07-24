<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Email – AbsensiKP</title>
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
        .auth-card { border:none; border-radius:20px; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:460px; overflow:hidden; }
        .auth-header { background:linear-gradient(135deg,#1e3a5f,#2d6a9f); padding:1.75rem; text-align:center; color:#fff; }
    </style>
</head>
<body>
    <div class="auth-card card">
        <div class="auth-header">
            <i class="fas fa-envelope-open-text fa-2x mb-2"></i>
            <h5 class="fw-bold mb-0">Verifikasi Email Anda</h5>
        </div>
        <div class="card-body p-4">
            <p class="text-muted mb-3">
                Terima kasih sudah mendaftar! Kami telah mengirim link verifikasi ke email Anda.
                Silakan cek inbox (atau folder spam) dan klik link tersebut untuk mengaktifkan akun.
            </p>

            @if(session('status') == 'verification-link-sent')
                <div class="alert alert-success py-2 small">
                    <i class="fas fa-check-circle me-2"></i>
                    Link verifikasi baru telah dikirim ke email Anda.
                </div>
            @endif

            <div class="d-flex gap-2 flex-column flex-sm-row">
                <form method="POST" action="{{ route('verification.send') }}" class="flex-grow-1">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="fas fa-paper-plane me-2"></i>Kirim Ulang Email
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
