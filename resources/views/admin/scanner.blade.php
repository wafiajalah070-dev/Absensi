@extends('layouts.app')

@section('title', 'Scanner Absensi')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@push('styles')
<style>
    #reader {
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid #e0e0e0 !important;
    }
    #reader video {
        border-radius: 10px;
    }
    /* Sembunyikan branding html5-qrcode */
    #reader__dashboard_section_csr span,
    #reader__dashboard_section_swaplink {
        display: none !important;
    }
    #reader__scan_region {
        background: #000;
    }
    .result-card {
        border-radius: 16px;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    .result-sukses  { border-color: #198754; background: #f0fdf4; }
    .result-gagal   { border-color: #dc3545; background: #fff5f5; }
    .result-info    { border-color: #0d6efd; background: #eff6ff; }
    .avatar-circle {
        width: 60px; height: 60px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; font-weight: 700; color: #fff;
        background: linear-gradient(135deg, #1e3a5f, #2d6a9f);
        flex-shrink: 0;
    }
    .scan-overlay {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.6);
        border-radius: 12px;
        z-index: 10;
    }
    .log-item {
        padding: 0.5rem 0.75rem;
        border-radius: 8px;
        font-size: 0.85rem;
        border-left: 3px solid;
        animation: fadeIn 0.3s ease;
    }
    .log-sukses { background: #f0fdf4; border-color: #198754; }
    .log-gagal  { background: #fff5f5; border-color: #dc3545; }
    @keyframes fadeIn { from { opacity:0; transform:translateY(-5px); } to { opacity:1; transform:translateY(0); } }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h5 class="fw-bold mb-0"><i class="fas fa-camera me-2 text-primary"></i>Scanner Absensi</h5>
            <p class="text-muted small mb-0">Scan QR karyawan menggunakan kamera perangkat ini</p>
        </div>
        <div class="text-end">
            <div class="fw-bold fs-5" id="jamSekarang">--:--:--</div>
            <div class="text-muted small" id="tanggalSekarang"></div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kolom Kamera --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Kamera</h6>
                    <div class="d-flex gap-2 align-items-center">
                        <select id="cameraSelect" class="form-select form-select-sm" style="width:auto">
                            <option value="">Pilih kamera...</option>
                        </select>
                        <button id="btnStart" class="btn btn-sm btn-success">
                            <i class="fas fa-play me-1"></i>Mulai
                        </button>
                        <button id="btnStop" class="btn btn-sm btn-danger d-none">
                            <i class="fas fa-stop me-1"></i>Stop
                        </button>
                    </div>
                </div>
                <div class="card-body p-3 position-relative">
                    <div id="reader" style="width:100%"></div>
                    <div class="scan-overlay d-none" id="processingOverlay">
                        <div class="text-center text-white">
                            <div class="spinner-border mb-2" role="status"></div>
                            <div>Memproses...</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-2">
                    <p class="text-muted small mb-0 text-center">
                        <i class="fas fa-info-circle me-1"></i>
                        Arahkan kamera ke QR code karyawan
                    </p>
                </div>
            </div>

            {{-- Mode Absensi --}}
            <div class="card mt-3">
                <div class="card-body py-2">
                    <label class="form-label fw-semibold small mb-2">Mode Absensi</label>
                    <div class="d-flex gap-2">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="modeAbsen"
                                   id="modeAuto" value="auto" checked>
                            <label class="form-check-label" for="modeAuto">
                                <strong>Auto</strong> <small class="text-muted">(masuk/keluar otomatis)</small>
                            </label>
                        </div>
                        <div class="form-check me-3">
                            <input class="form-check-input" type="radio" name="modeAbsen"
                                   id="modeMasuk" value="masuk">
                            <label class="form-check-label" for="modeMasuk">
                                <strong>Masuk</strong>
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="modeAbsen"
                                   id="modeKeluar" value="keluar">
                            <label class="form-check-label" for="modeKeluar">
                                <strong>Keluar</strong>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Hasil --}}
        <div class="col-lg-6">
            {{-- Hasil Scan Terakhir --}}
            <div id="hasilScan" class="card result-card result-info mb-3 d-none">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="avatar-circle" id="avatarInitial">?</div>
                        <div>
                            <h6 class="fw-bold mb-0" id="namaKaryawan">-</h6>
                            <small class="text-muted" id="infoKaryawan">-</small>
                        </div>
                        <div class="ms-auto text-end">
                            <div class="small text-muted">Status</div>
                            <span id="badgeStatus" class="badge">-</span>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <div class="text-muted small">Jam Masuk</div>
                                <div class="fw-bold" id="jamMasuk">-</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="bg-light rounded p-2 text-center">
                                <div class="text-muted small">Jam Keluar</div>
                                <div class="fw-bold" id="jamKeluar">-</div>
                            </div>
                        </div>
                    </div>

                    <div id="alertHasil" class="alert mb-3 py-2 small d-none"></div>

                    <div id="tombolKonfirmasi" class="d-flex gap-2 d-none">
                        <button id="btnKonfirmasi" class="btn btn-success flex-grow-1 fw-semibold">
                            <i class="fas fa-check me-2"></i><span id="labelKonfirmasi">Konfirmasi Absen</span>
                        </button>
                        <button id="btnBatal" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Batal
                        </button>
                    </div>

                    <div id="pesanHasil" class="d-none"></div>
                </div>
            </div>

            {{-- Placeholder sebelum scan --}}
            <div id="placeholderScan" class="card text-center py-5">
                <div class="card-body">
                    <i class="fas fa-qrcode fa-4x text-muted opacity-25 mb-3"></i>
                    <p class="text-muted">Belum ada scan</p>
                    <small class="text-muted">Mulai kamera dan scan QR karyawan</small>
                </div>
            </div>

            {{-- Log Absensi Hari Ini --}}
            <div class="card mt-3">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
                    <h6 class="fw-bold mb-0"><i class="fas fa-list me-2 text-primary"></i>Log Absensi</h6>
                    <button class="btn btn-sm btn-outline-secondary" id="btnClearLog">Bersihkan</button>
                </div>
                <div class="card-body p-2" id="logAbsensi" style="max-height:250px;overflow-y:auto">
                    <p class="text-muted small text-center py-3 mb-0" id="logKosong">Belum ada aktivitas</p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';
const URL_STATUS  = '{{ route("admin.scanner.status", ":token") }}';
const URL_PROSES  = '{{ route("admin.scanner.proses") }}';

let html5QrCode  = null;
let scanning     = false;
let lastScanned  = null;
let scanCooldown = false;
let currentToken = null;
let currentAksi  = null;

// ── Jam realtime ──────────────────────────────────────────────
function updateJam() {
    const now  = new Date();
    const h    = String(now.getHours()).padStart(2,'0');
    const m    = String(now.getMinutes()).padStart(2,'0');
    const s    = String(now.getSeconds()).padStart(2,'0');
    document.getElementById('jamSekarang').textContent = `${h}:${m}:${s}`;

    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    document.getElementById('tanggalSekarang').textContent =
        `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
}
updateJam();
setInterval(updateJam, 1000);

// ── Init kamera ───────────────────────────────────────────────
Html5Qrcode.getCameras().then(cameras => {
    const sel = document.getElementById('cameraSelect');
    cameras.forEach((cam, i) => {
        const opt = document.createElement('option');
        opt.value = cam.id;
        opt.text  = cam.label || `Kamera ${i + 1}`;
        sel.appendChild(opt);
    });
    // Otomatis pilih kamera belakang jika ada
    const belakang = cameras.find(c => /back|rear|environment/i.test(c.label));
    if (belakang) sel.value = belakang.id;
    else if (cameras.length) sel.value = cameras[0].id;
}).catch(err => console.warn('Gagal ambil kamera:', err));

// ── Mulai scan ────────────────────────────────────────────────
document.getElementById('btnStart').addEventListener('click', () => {
    const camId = document.getElementById('cameraSelect').value;
    if (!camId) { alert('Pilih kamera terlebih dahulu.'); return; }

    html5QrCode = new Html5Qrcode('reader');
    html5QrCode.start(
        camId,
        { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 },
        onScanSuccess,
        () => {}
    ).then(() => {
        scanning = true;
        document.getElementById('btnStart').classList.add('d-none');
        document.getElementById('btnStop').classList.remove('d-none');
    }).catch(err => alert('Gagal akses kamera: ' + err));
});

// ── Stop scan ─────────────────────────────────────────────────
document.getElementById('btnStop').addEventListener('click', stopScanner);

function stopScanner() {
    if (html5QrCode && scanning) {
        html5QrCode.stop().then(() => {
            scanning = false;
            document.getElementById('btnStart').classList.remove('d-none');
            document.getElementById('btnStop').classList.add('d-none');
        });
    }
}

// ── Callback scan berhasil ────────────────────────────────────
function onScanSuccess(decodedText) {
    if (scanCooldown) return;
    scanCooldown = true;

    // Ekstrak token dari URL jika format URL lengkap
    let token = decodedText;
    const match = decodedText.match(/\/scan\/([a-f0-9\-]{36})/i);
    if (match) token = match[1];

    if (token === lastScanned) {
        setTimeout(() => { scanCooldown = false; }, 2000);
        return;
    }
    lastScanned = token;

    // Tampilkan overlay processing
    document.getElementById('processingOverlay').classList.remove('d-none');

    // Fetch status karyawan
    const url = URL_STATUS.replace(':token', token);
    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('processingOverlay').classList.add('d-none');
            tampilkanHasil(token, data);
        })
        .catch(() => {
            document.getElementById('processingOverlay').classList.add('d-none');
            tampilkanError('Gagal koneksi ke server.');
        })
        .finally(() => {
            setTimeout(() => { scanCooldown = false; }, 3000);
        });
}

// ── Tampilkan hasil scan ──────────────────────────────────────
function tampilkanHasil(token, data) {
    document.getElementById('placeholderScan').classList.add('d-none');
    const card = document.getElementById('hasilScan');
    card.classList.remove('d-none', 'result-sukses', 'result-gagal', 'result-info');

    if (!data.valid) {
        card.classList.add('result-gagal');
        document.getElementById('avatarInitial').textContent = '!';
        document.getElementById('namaKaryawan').textContent  = 'QR Tidak Valid';
        document.getElementById('infoKaryawan').textContent  = data.pesan;
        document.getElementById('badgeStatus').textContent   = 'Error';
        document.getElementById('badgeStatus').className     = 'badge bg-danger';
        document.getElementById('jamMasuk').textContent      = '-';
        document.getElementById('jamKeluar').textContent     = '-';
        document.getElementById('tombolKonfirmasi').classList.add('d-none');
        document.getElementById('pesanHasil').classList.add('d-none');
        document.getElementById('alertHasil').classList.add('d-none');
        tambahLog(data.pesan, false);
        return;
    }

    card.classList.add('result-info');
    document.getElementById('avatarInitial').textContent = data.nama.charAt(0).toUpperCase();
    document.getElementById('namaKaryawan').textContent  = data.nama;
    document.getElementById('infoKaryawan').textContent  = `${data.jabatan} | ${data.divisi} | NIP: ${data.nip}`;
    document.getElementById('jamMasuk').textContent      = data.jam_masuk  ?? '-';
    document.getElementById('jamKeluar').textContent     = data.jam_keluar ?? '-';
    document.getElementById('alertHasil').classList.add('d-none');
    document.getElementById('pesanHasil').classList.add('d-none');

    // Tentukan aksi
    const mode = document.querySelector('input[name="modeAbsen"]:checked').value;
    let aksi    = data.aksi_berikutnya;
    if (mode !== 'auto') aksi = mode;

    currentToken = token;
    currentAksi  = aksi;

    if (aksi === 'selesai') {
        document.getElementById('badgeStatus').textContent  = 'Selesai';
        document.getElementById('badgeStatus').className    = 'badge bg-success';
        document.getElementById('tombolKonfirmasi').classList.add('d-none');
        const alert = document.getElementById('alertHasil');
        alert.className = 'alert alert-success mb-3 py-2 small';
        alert.textContent = `✅ ${data.nama} sudah absen masuk & keluar hari ini.`;
        alert.classList.remove('d-none');
    } else if (aksi === 'masuk') {
        document.getElementById('badgeStatus').textContent  = 'Belum Masuk';
        document.getElementById('badgeStatus').className    = 'badge bg-warning text-dark';
        document.getElementById('labelKonfirmasi').textContent = 'Absen Masuk';
        document.getElementById('btnKonfirmasi').className  = 'btn btn-success flex-grow-1 fw-semibold';
        document.getElementById('tombolKonfirmasi').classList.remove('d-none');
    } else if (aksi === 'keluar') {
        document.getElementById('badgeStatus').textContent  = 'Sudah Masuk';
        document.getElementById('badgeStatus').className    = 'badge bg-primary';
        document.getElementById('labelKonfirmasi').textContent = 'Absen Keluar';
        document.getElementById('btnKonfirmasi').className  = 'btn btn-warning flex-grow-1 fw-semibold';
        document.getElementById('tombolKonfirmasi').classList.remove('d-none');
    }
}

// ── Konfirmasi absen ──────────────────────────────────────────
document.getElementById('btnKonfirmasi').addEventListener('click', () => {
    if (!currentToken || !currentAksi) return;

    document.getElementById('btnKonfirmasi').disabled = true;
    document.getElementById('btnKonfirmasi').innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

    fetch(URL_PROSES, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ token: currentToken, aksi: currentAksi }),
    })
    .then(r => r.json())
    .then(data => {
        const card   = document.getElementById('hasilScan');
        const pesan  = document.getElementById('pesanHasil');
        const tombol = document.getElementById('tombolKonfirmasi');

        tombol.classList.add('d-none');

        if (data.sukses) {
            card.classList.remove('result-info');
            card.classList.add('result-sukses');
            document.getElementById('badgeStatus').textContent = currentAksi === 'masuk' ? 'Masuk' : 'Keluar';
            document.getElementById('badgeStatus').className   = 'badge bg-success';

            if (currentAksi === 'masuk') document.getElementById('jamMasuk').textContent  = data.jam;
            else                         document.getElementById('jamKeluar').textContent = data.jam;

            pesan.innerHTML  = `<div class="alert alert-success py-2 small mb-0">✅ ${data.pesan}</div>`;
            pesan.classList.remove('d-none');
            tambahLog(data.pesan, true);

            // Reset scan setelah 4 detik supaya bisa scan berikutnya
            setTimeout(() => {
                lastScanned  = null;
                currentToken = null;
                currentAksi  = null;
            }, 4000);
        } else {
            card.classList.remove('result-info');
            card.classList.add('result-gagal');
            pesan.innerHTML = `<div class="alert alert-danger py-2 small mb-0">❌ ${data.pesan}</div>`;
            pesan.classList.remove('d-none');
            tambahLog(data.pesan, false);
        }
    })
    .catch(() => {
        document.getElementById('pesanHasil').innerHTML = '<div class="alert alert-danger py-2 small mb-0">❌ Gagal koneksi ke server.</div>';
        document.getElementById('pesanHasil').classList.remove('d-none');
    })
    .finally(() => {
        document.getElementById('btnKonfirmasi').disabled = false;
        document.getElementById('btnKonfirmasi').innerHTML =
            `<i class="fas fa-check me-2"></i><span id="labelKonfirmasi">Konfirmasi Absen</span>`;
    });
});

// ── Batal ─────────────────────────────────────────────────────
document.getElementById('btnBatal').addEventListener('click', () => {
    lastScanned  = null;
    currentToken = null;
    currentAksi  = null;
    document.getElementById('hasilScan').classList.add('d-none');
    document.getElementById('placeholderScan').classList.remove('d-none');
});

// ── Tampilkan error ───────────────────────────────────────────
function tampilkanError(pesan) {
    document.getElementById('placeholderScan').classList.add('d-none');
    const card = document.getElementById('hasilScan');
    card.classList.remove('d-none', 'result-sukses', 'result-info');
    card.classList.add('result-gagal');
    document.getElementById('avatarInitial').textContent = '!';
    document.getElementById('namaKaryawan').textContent  = 'Error';
    document.getElementById('infoKaryawan').textContent  = pesan;
    document.getElementById('tombolKonfirmasi').classList.add('d-none');
}

// ── Log aktivitas ─────────────────────────────────────────────
function tambahLog(pesan, sukses) {
    const log = document.getElementById('logAbsensi');
    document.getElementById('logKosong').classList.add('d-none');
    const now = new Date();
    const jam = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`;
    const div = document.createElement('div');
    div.className = `log-item mb-1 ${sukses ? 'log-sukses' : 'log-gagal'}`;
    div.innerHTML = `<span class="text-muted me-2">${jam}</span> ${sukses ? '✅' : '❌'} ${pesan}`;
    log.insertBefore(div, log.firstChild);
}

document.getElementById('btnClearLog').addEventListener('click', () => {
    document.getElementById('logAbsensi').innerHTML =
        '<p class="text-muted small text-center py-3 mb-0" id="logKosong">Belum ada aktivitas</p>';
});
</script>
@endpush
