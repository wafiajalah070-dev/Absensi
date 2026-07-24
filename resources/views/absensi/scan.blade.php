<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi – {{ $karyawan->name }}</title>
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
        .absen-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 420px;
            width: 100%;
        }
        .absen-header {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            border-radius: 20px 20px 0 0;
            padding: 1.75rem;
            text-align: center;
            color: #fff;
        }
        .time-display {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e3a5f;
            letter-spacing: 2px;
        }
        .btn-absen {
            border-radius: 12px;
            padding: 0.85rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
        .status-box {
            border-radius: 12px;
            padding: 1rem;
        }
    </style>
</head>
<body>
    <div class="absen-card card">
        <div class="absen-header">
            <i class="fas fa-fingerprint fa-2x mb-2"></i>
            <h5 class="mb-0 fw-bold">{{ $karyawan->name }}</h5>
            <p class="mb-0 opacity-75 small">
                {{ $karyawan->jabatan ?? '' }}
                {{ $karyawan->divisi ? '| '.$karyawan->divisi : '' }}
            </p>
            <p class="mb-0 opacity-60 small mt-1">{{ now()->translatedFormat('l, d F Y') }}</p>
        </div>

        <div class="card-body p-4">

            {{-- Notifikasi --}}
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-check-circle fa-lg"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
                    <i class="fas fa-exclamation-circle fa-lg"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            {{-- Jam saat ini --}}
            <div class="text-center mb-4">
                <div class="time-display" id="clock">--:--:--</div>
                <small class="text-muted">Waktu saat ini</small>
            </div>

            {{-- Status absensi hari ini --}}
            @if($absensiHari)
                <div class="status-box mb-3" style="background:#f0fdf4;border:1px solid #86efac">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-success fw-semibold small"><i class="fas fa-sign-in-alt me-1"></i>Jam Masuk</div>
                            <div class="fw-bold">{{ \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i') }}</div>
                        </div>
                        <div class="text-end">
                            <div class="text-muted fw-semibold small"><i class="fas fa-sign-out-alt me-1"></i>Jam Keluar</div>
                            <div class="fw-bold">
                                {{ $absensiHari->jam_keluar ? \Carbon\Carbon::parse($absensiHari->jam_keluar)->format('H:i') : '–' }}
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tombol Absensi --}}
            @if(!$absensiHari)
                <form method="POST" action="{{ route('absensi.proses', $token) }}">
                    @csrf
                    <input type="hidden" name="aksi" value="masuk">
                    <button type="submit" class="btn btn-success w-100 btn-absen">
                        <i class="fas fa-sign-in-alt me-2"></i>Absen Masuk
                    </button>
                </form>
            @elseif(!$absensiHari->jam_keluar)
                <form method="POST" action="{{ route('absensi.proses', $token) }}">
                    @csrf
                    <input type="hidden" name="aksi" value="keluar">
                    <button type="submit" class="btn btn-warning w-100 btn-absen">
                        <i class="fas fa-sign-out-alt me-2"></i>Absen Keluar
                    </button>
                </form>
            @else
                <div class="text-center py-3">
                    <i class="fas fa-check-circle text-success fa-3x mb-2"></i>
                    <p class="fw-semibold text-success mb-0">Absensi hari ini sudah lengkap!</p>
                    <small class="text-muted">Sampai jumpa besok 👋</small>
                </div>
            @endif

            <hr class="my-3">
            <p class="text-center text-muted small mb-0">
                <i class="fas fa-shield-alt me-1"></i>
                Absensi terverifikasi • {{ config('app.name') }}
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateClock() {
            const now = new Date();
            const h = String(now.getHours()).padStart(2,'0');
            const m = String(now.getMinutes()).padStart(2,'0');
            const s = String(now.getSeconds()).padStart(2,'0');
            document.getElementById('clock').textContent = `${h}:${m}:${s}`;
        }
        updateClock();
        setInterval(updateClock, 1000);
    </script>
</body>
</html>
