<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Saya – {{ $user->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3a5f 0%, #0d2137 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .qr-card {
            background: #fff;
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            max-width: 380px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .qr-card svg, .qr-card img {
            max-width: 220px;
            width: 100%;
        }
        .nama-badge {
            background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
            color: #fff;
            border-radius: 50px;
            padding: 0.4rem 1.2rem;
            font-size: 0.9rem;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .keterangan {
            font-size: 0.8rem;
            color: #666;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px dashed #ddd;
        }
        .btn-kembali {
            margin-top: 1.5rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 0.9rem;
        }
        .btn-kembali:hover { color: #fff; }

        /* Animasi pulse pada QR */
        .qr-wrapper {
            display: inline-block;
            padding: 12px;
            border-radius: 16px;
            border: 3px solid #1e3a5f;
            animation: pulse-border 2s infinite;
            margin: 1rem 0;
        }
        @keyframes pulse-border {
            0%, 100% { border-color: #1e3a5f; box-shadow: 0 0 0 0 rgba(30,58,95,0.3); }
            50%       { border-color: #2d6a9f; box-shadow: 0 0 0 8px rgba(30,58,95,0); }
        }

        /* Sembunyikan saat print */
        @media print {
            body { background: #fff !important; }
            .no-print { display: none !important; }
            .qr-card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>
<body>
    <div class="qr-card">
        {{-- Header --}}
        <div class="mb-2">
            <i class="fas fa-fingerprint text-primary" style="font-size:2rem"></i>
            <p class="text-muted small mb-1 mt-1">AbsensiKP</p>
        </div>

        {{-- Nama --}}
        <div class="nama-badge">{{ $user->name }}</div>

        {{-- QR Code --}}
        <div class="qr-wrapper">
            {!! QrCode::size(200)->generate($user->qr_token) !!}
        </div>

        {{-- Info karyawan --}}
        <div class="mb-2">
            @if($user->nip)
                <div class="fw-semibold">NIP: {{ $user->nip }}</div>
            @endif
            @if($user->jabatan)
                <div class="text-muted small">{{ $user->jabatan }}{{ $user->divisi ? ' – '.$user->divisi : '' }}</div>
            @endif
        </div>

        {{-- Instruksi --}}
        <div class="keterangan">
            <i class="fas fa-info-circle me-1"></i>
            Tunjukkan QR ini ke petugas absensi.<br>
            Tidak perlu internet — QR tersimpan di HP Anda.
        </div>

        {{-- Tombol --}}
        <div class="mt-3 no-print">
            <button onclick="window.print()" class="btn btn-outline-primary btn-sm me-2">
                <i class="fas fa-print me-1"></i>Print / Simpan
            </button>
        </div>
    </div>

    <a href="{{ route('karyawan.dashboard') }}" class="btn-kembali no-print">
        <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
