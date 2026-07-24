<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Absensi – {{ $user->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { box-sizing: border-box; }
        body {
            background: linear-gradient(160deg, #0f2942 0%, #1a5276 50%, #0f2942 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            padding: 0;
            margin: 0;
        }

        /* ── Header ── */
        .top-bar {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .top-bar .logo { color:#fff; font-weight:700; font-size:1rem; }
        .top-bar .user-info { color:rgba(255,255,255,0.8); font-size:0.8rem; text-align:right; }

        /* ── Jam besar ── */
        .time-section {
            text-align: center;
            padding: 2rem 1rem 1rem;
            color: #fff;
        }
        .big-clock {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: 4px;
            line-height: 1;
            text-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        .big-date { font-size: 0.95rem; color: rgba(255,255,255,0.7); margin-top:0.25rem; }

        /* ── Card utama ── */
        .main-card {
            background: #fff;
            border-radius: 28px 28px 0 0;
            min-height: calc(100vh - 220px);
            padding: 1.75rem 1.25rem;
            margin-top: 1.5rem;
        }

        /* ── Status lokasi ── */
        .lokasi-bar {
            border-radius: 50px;
            padding: 0.6rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s;
        }
        .lokasi-loading { background:#f0f4ff; color:#5b6abf; border: 1px solid #c5ceff; }
        .lokasi-ok      { background:#f0fdf4; color:#166534; border: 1px solid #86efac; }
        .lokasi-jauh    { background:#fff5f5; color:#991b1b; border: 1px solid #fca5a5; }
        .lokasi-dot {
            width:10px; height:10px; border-radius:50%; flex-shrink:0;
            animation: pulse-dot 1.5s infinite;
        }
        .dot-loading { background:#5b6abf; }
        .dot-ok      { background:#16a34a; animation: none; }
        .dot-jauh    { background:#dc2626; animation: none; }
        @keyframes pulse-dot {
            0%,100% { transform:scale(1); opacity:1; }
            50%      { transform:scale(1.4); opacity:0.6; }
        }

        /* ── Status absensi hari ini ── */
        .status-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }
        .status-box {
            border-radius: 16px;
            padding: 1rem;
            text-align: center;
            border: 2px solid;
        }
        .status-box.masuk  { background:#f0fdf4; border-color:#86efac; }
        .status-box.keluar { background:#eff6ff; border-color:#93c5fd; }
        .status-box.empty  { background:#f9fafb; border-color:#e5e7eb; }
        .status-box .label { font-size:0.75rem; color:#6b7280; margin-bottom:0.25rem; }
        .status-box .value { font-size:1.4rem; font-weight:800; color:#111; }
        .status-box .icon  { font-size:1.25rem; margin-bottom:0.25rem; }

        /* ── Tombol absensi ── */
        .btn-absen {
            width: 100%;
            border-radius: 18px;
            padding: 1.1rem;
            font-size: 1.1rem;
            font-weight: 700;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
            margin-bottom: 0.75rem;
        }
        .btn-masuk  { background: linear-gradient(135deg,#16a34a,#15803d); color:#fff; box-shadow: 0 6px 20px rgba(22,163,74,0.35); }
        .btn-keluar { background: linear-gradient(135deg,#2563eb,#1d4ed8); color:#fff; box-shadow: 0 6px 20px rgba(37,99,235,0.35); }
        .btn-done   { background: #f3f4f6; color:#9ca3af; cursor:not-allowed; }
        .btn-absen:active:not(:disabled) { transform: scale(0.97); }
        .btn-absen:disabled { opacity:0.6; cursor:not-allowed; transform:none !important; }

        /* ── Info jarak ── */
        .jarak-info {
            background: #f8fafc;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.25rem;
        }

        /* ── Notifikasi ── */
        .notif {
            border-radius: 14px;
            padding: 0.85rem 1rem;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 0.6rem;
            animation: slideDown 0.3s ease;
        }
        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .notif-sukses { background:#f0fdf4; border: 1.5px solid #86efac; color:#166534; }
        .notif-gagal  { background:#fff5f5; border: 1.5px solid #fca5a5; color:#991b1b; }

        /* ── Peta mini ── */
        #peta {
            height: 180px;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 1rem;
            border: 2px solid #e5e7eb;
        }

        /* ── Link navigasi ── */
        .nav-links {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .nav-link-btn {
            flex: 1;
            text-align: center;
            padding: 0.65rem;
            border-radius: 12px;
            font-size: 0.8rem;
            text-decoration: none;
            border: 1.5px solid #e5e7eb;
            color: #374151;
            transition: all 0.2s;
        }
        .nav-link-btn:hover { background:#f3f4f6; color:#111; }
    </style>
</head>
<body>
    <!-- Top Bar -->
    <div class="top-bar">
        <div class="logo"><i class="fas fa-map-marker-alt me-2"></i>AbsensiKP</div>
        <div class="user-info">
            <div class="fw-semibold text-white">{{ $user->name }}</div>
            <div>{{ $user->jabatan ?? $user->divisi ?? 'Karyawan' }}</div>
        </div>
    </div>

    <!-- Jam & Tanggal -->
    <div class="time-section">
        <div class="big-clock" id="bigClock">00:00:00</div>
        <div class="big-date" id="bigDate"></div>
    </div>

    <!-- Card Utama -->
    <div class="main-card">

        <!-- Status Lokasi -->
        <div class="lokasi-bar lokasi-loading" id="lokasiBar">
            <div class="lokasi-dot dot-loading" id="lokasiDot"></div>
            <span id="lokasiTeks"><i class="fas fa-spinner fa-spin me-1"></i> Mendeteksi lokasi Anda...</span>
        </div>

        <!-- Notifikasi -->
        <div id="notifArea"></div>

        <!-- Status Absensi Hari Ini -->
        <div class="status-row">
            <div class="status-box {{ $absensiHari?->jam_masuk ? 'masuk' : 'empty' }}">
                <div class="icon">{{ $absensiHari?->jam_masuk ? '✅' : '⏰' }}</div>
                <div class="label">Jam Masuk</div>
                <div class="value" id="dispJamMasuk">
                    {{ $absensiHari?->jam_masuk ? \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i') : '--:--' }}
                </div>
            </div>
            <div class="status-box {{ $absensiHari?->jam_keluar ? 'keluar' : 'empty' }}">
                <div class="icon">{{ $absensiHari?->jam_keluar ? '🏁' : '🚪' }}</div>
                <div class="label">Jam Keluar</div>
                <div class="value" id="dispJamKeluar">
                    {{ $absensiHari?->jam_keluar ? \Carbon\Carbon::parse($absensiHari->jam_keluar)->format('H:i') : '--:--' }}
                </div>
            </div>
        </div>

        <!-- Info Jarak -->
        <div class="jarak-info" id="jarakInfo" style="display:none!important">
            <i class="fas fa-ruler-horizontal text-primary"></i>
            <span id="jarakTeks">Menghitung jarak...</span>
            @if($kantor && $kantor->latitude)
                <span class="ms-auto text-primary fw-semibold">Radius: {{ $kantor->radius_meter }}m</span>
            @endif
        </div>

        <!-- Peta Mini -->
        <div id="peta"></div>

        <!-- Tombol Absensi -->
        @if(!$absensiHari)
            <button class="btn-absen btn-masuk" id="btnMasuk" disabled>
                <i class="fas fa-sign-in-alt"></i> Absen Masuk
            </button>
        @elseif(!$absensiHari->jam_keluar)
            <button class="btn-absen btn-masuk" disabled style="display:none" id="btnMasuk"></button>
            <button class="btn-absen btn-keluar" id="btnKeluar" disabled>
                <i class="fas fa-sign-out-alt"></i> Absen Keluar
            </button>
        @else
            <button class="btn-absen btn-done" disabled>
                <i class="fas fa-check-circle"></i> Absensi Hari Ini Selesai
            </button>
        @endif

        <!-- Link Navigasi -->
        <div class="nav-links">
            <a href="{{ route('karyawan.dashboard') }}" class="nav-link-btn">
                <i class="fas fa-home me-1"></i>Dashboard
            </a>
            <a href="{{ route('karyawan.riwayat') }}" class="nav-link-btn">
                <i class="fas fa-history me-1"></i>Riwayat
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Leaflet untuk peta -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script>
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const URL_GPS = '{{ route("karyawan.absensi.proses") }}';

    @if($kantor && $kantor->latitude)
    const KANTOR_LAT    = {{ $kantor->latitude }};
    const KANTOR_LNG    = {{ $kantor->longitude }};
    const KANTOR_RADIUS = {{ $kantor->radius_meter }};
    const KANTOR_NAMA   = '{{ addslashes($kantor->nama_kantor) }}';
    const ADA_KANTOR    = true;
    @else
    const KANTOR_LAT = null; const KANTOR_LNG = null;
    const KANTOR_RADIUS = 100; const KANTOR_NAMA = 'Kantor';
    const ADA_KANTOR = false;
    @endif

    let userLat = null, userLng = null, userAlamat = '';
    let map = null, markerUser = null, markerKantor = null, lingkaran = null;

    // ── Jam realtime ────────────────────────────────
    const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    const months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2,'0');
        const m = String(now.getMinutes()).padStart(2,'0');
        const s = String(now.getSeconds()).padStart(2,'0');
        document.getElementById('bigClock').textContent = `${h}:${m}:${s}`;
        document.getElementById('bigDate').textContent  =
            `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    // ── Init Peta ────────────────────────────────────
    function initPeta(lat, lng) {
        if (!map) {
            map = L.map('peta', { zoomControl:true, attributionControl:false });
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        }
        map.setView([lat, lng], 17);

        // Marker user
        const iconUser = L.divIcon({
            html: '<div style="background:#2563eb;width:16px;height:16px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
            iconSize:[16,16], iconAnchor:[8,8], className:''
        });
        if (markerUser) markerUser.setLatLng([lat, lng]);
        else markerUser = L.marker([lat, lng], {icon: iconUser}).addTo(map).bindPopup('📍 Posisi Anda');

        // Marker & radius kantor
        if (ADA_KANTOR) {
            const iconKantor = L.divIcon({
                html: '<div style="background:#dc2626;width:18px;height:18px;border-radius:50%;border:3px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>',
                iconSize:[18,18], iconAnchor:[9,9], className:''
            });
            if (!markerKantor) {
                markerKantor = L.marker([KANTOR_LAT, KANTOR_LNG], {icon: iconKantor})
                    .addTo(map).bindPopup('🏢 ' + KANTOR_NAMA);
                lingkaran = L.circle([KANTOR_LAT, KANTOR_LNG], {
                    radius: KANTOR_RADIUS,
                    color: '#16a34a', fillColor: '#16a34a', fillOpacity: 0.1,
                    weight: 2, dashArray: '6,4'
                }).addTo(map);
            }
            // Fit bounds antara user dan kantor
            map.fitBounds([
                [lat, lng],
                [KANTOR_LAT, KANTOR_LNG]
            ], { padding: [30, 30] });
        }
    }

    // ── Hitung Jarak (Haversine) ──────────────────────
    function hitungJarak(lat1, lng1, lat2, lng2) {
        const R  = 6371000;
        const d1 = lat1 * Math.PI / 180;
        const d2 = lat2 * Math.PI / 180;
        const a  = Math.sin((lat2-lat1)*Math.PI/360) ** 2
                 + Math.cos(d1) * Math.cos(d2)
                 * Math.sin((lng2-lng1)*Math.PI/360) ** 2;
        return Math.round(R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
    }

    // ── Deteksi GPS ──────────────────────────────────
    function deteksiLokasi() {
        if (!navigator.geolocation) {
            setLokasiStatus('error', 'Browser tidak mendukung GPS.');
            aktifkanTombol(true); // Izinkan absen tanpa GPS
            return;
        }
        navigator.geolocation.getCurrentPosition(
            onLokasiOk,
            onLokasigagal,
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }

    function onLokasiOk(pos) {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;

        initPeta(userLat, userLng);
        document.getElementById('jarakInfo').style.removeProperty('display');

        // Reverse geocode (optional, pakai nominatim)
        fetch(`https://nominatim.openstreetmap.org/reverse?lat=${userLat}&lon=${userLng}&format=json`)
            .then(r => r.json())
            .then(d => { userAlamat = d.display_name || `${userLat},${userLng}`; })
            .catch(() => { userAlamat = `${userLat.toFixed(6)}, ${userLng.toFixed(6)}`; });

        if (ADA_KANTOR) {
            const jarak = hitungJarak(userLat, userLng, KANTOR_LAT, KANTOR_LNG);
            const diArea = jarak <= KANTOR_RADIUS;

            document.getElementById('jarakTeks').textContent =
                `Jarak ke kantor: ${jarak >= 1000 ? (jarak/1000).toFixed(1)+'km' : jarak+'m'}`;

            if (diArea) {
                setLokasiStatus('ok', `✅ Anda dalam area kantor (${jarak}m)`);
            } else {
                setLokasiStatus('jauh', `⚠️ Anda di luar area kantor (${jarak}m dari ${KANTOR_RADIUS}m radius)`);
            }
            aktifkanTombol(diArea);
        } else {
            setLokasiStatus('ok', `✅ Lokasi terdeteksi (${userLat.toFixed(5)}, ${userLng.toFixed(5)})`);
            document.getElementById('jarakTeks').textContent = 'Kantor belum dikonfigurasi – lokasi bebas';
            aktifkanTombol(true);
        }
    }

    function onLokasigagal(err) {
        const pesan = {
            1: 'Akses lokasi ditolak. Izinkan lokasi di browser.',
            2: 'Sinyal GPS lemah. Coba di tempat terbuka.',
            3: 'Timeout. Coba lagi.',
        }[err.code] || 'Gagal deteksi lokasi.';
        setLokasiStatus('error', '❌ ' + pesan);
        aktifkanTombol(false);
    }

    function setLokasiStatus(type, teks) {
        const bar = document.getElementById('lokasiBar');
        const dot = document.getElementById('lokasiDot');
        const txt = document.getElementById('lokasiTeks');
        bar.className = 'lokasi-bar';
        dot.className = 'lokasi-dot';
        if (type === 'ok')    { bar.classList.add('lokasi-ok');   dot.classList.add('dot-ok');   }
        if (type === 'jauh')  { bar.classList.add('lokasi-jauh'); dot.classList.add('dot-jauh'); }
        if (type === 'error') { bar.classList.add('lokasi-jauh'); dot.classList.add('dot-jauh'); }
        if (type === 'loading') { bar.classList.add('lokasi-loading'); dot.classList.add('dot-loading'); }
        txt.innerHTML = teks;
    }

    function aktifkanTombol(aktif) {
        const btnM = document.getElementById('btnMasuk');
        const btnK = document.getElementById('btnKeluar');
        if (btnM) btnM.disabled = !aktif;
        if (btnK) btnK.disabled = !aktif;
    }

    // ── Proses Absensi ────────────────────────────────
    function prosesAbsen(aksi, btn) {
        btn.disabled = true;
        const label = aksi === 'masuk' ? 'Absen Masuk' : 'Absen Keluar';
        btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...`;

        fetch(URL_GPS, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({
                aksi,
                latitude:  userLat  ?? 0,
                longitude: userLng  ?? 0,
                alamat:    userAlamat || `${userLat},${userLng}`,
            }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.sukses) {
                tampilNotif('sukses', data.pesan);
                if (aksi === 'masuk') {
                    document.getElementById('dispJamMasuk').textContent = data.jam;
                    document.querySelector('.status-box:first-child').classList.remove('empty');
                    document.querySelector('.status-box:first-child').classList.add('masuk');
                    document.querySelector('.status-box:first-child .icon').textContent = '✅';
                    // Ganti ke tombol keluar
                    btn.outerHTML = `<button class="btn-absen btn-keluar" id="btnKeluar" onclick="prosesAbsen('keluar',this)">
                        <i class="fas fa-sign-out-alt"></i> Absen Keluar
                    </button>`;
                    deteksiLokasi(); // Re-detect untuk tombol keluar
                } else {
                    document.getElementById('dispJamKeluar').textContent = data.jam;
                    document.querySelector('.status-box:last-child').classList.remove('empty');
                    document.querySelector('.status-box:last-child').classList.add('keluar');
                    document.querySelector('.status-box:last-child .icon').textContent = '🏁';
                    btn.outerHTML = `<button class="btn-absen btn-done" disabled>
                        <i class="fas fa-check-circle"></i> Absensi Hari Ini Selesai
                    </button>`;
                }
            } else {
                tampilNotif('gagal', data.pesan);
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-${aksi === 'masuk' ? 'sign-in-alt' : 'sign-out-alt'}"></i> Absen ${aksi.charAt(0).toUpperCase()+aksi.slice(1)}`;
            }
        })
        .catch(() => {
            tampilNotif('gagal', 'Gagal terhubung ke server. Periksa koneksi internet.');
            btn.disabled = false;
            btn.innerHTML = `<i class="fas fa-${aksi === 'masuk' ? 'sign-in-alt' : 'sign-out-alt'}"></i> Absen ${aksi.charAt(0).toUpperCase()+aksi.slice(1)}`;
        });
    }

    function tampilNotif(type, pesan) {
        const area = document.getElementById('notifArea');
        const icon = type === 'sukses' ? '✅' : '❌';
        area.innerHTML = `<div class="notif notif-${type}">${icon} ${pesan}</div>`;
        setTimeout(() => { area.innerHTML = ''; }, 5000);
    }

    // ── Event listener tombol ─────────────────────────
    document.addEventListener('click', e => {
        if (e.target.closest('#btnMasuk'))  prosesAbsen('masuk',  e.target.closest('#btnMasuk'));
        if (e.target.closest('#btnKeluar')) prosesAbsen('keluar', e.target.closest('#btnKeluar'));
    });

    // ── Mulai deteksi lokasi ──────────────────────────
    deteksiLokasi();
    </script>
</body>
</html>
