@extends('layouts.app')

@section('title', 'Pengaturan Kantor')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<style>
    #peta-admin { height: 350px; border-radius: 12px; border: 2px solid #e0e0e0; }
</style>
@endpush

@section('content')
    <h5 class="fw-bold mb-1">Pengaturan Kantor</h5>
    <p class="text-muted small mb-4">Atur lokasi & radius area absensi GPS karyawan</p>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-map-marker-alt me-2 text-danger"></i>Pilih Lokasi Kantor</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-2">Klik pada peta untuk menentukan lokasi kantor, atau gunakan tombol deteksi otomatis.</p>
                    <button type="button" class="btn btn-sm btn-outline-primary mb-3" id="btnDeteksiKantor">
                        <i class="fas fa-crosshairs me-1"></i>Deteksi Lokasi Saya Sekarang
                    </button>
                    <div id="peta-admin" class="mb-2"></div>
                    <small class="text-muted"><i class="fas fa-info-circle me-1"></i>Klik peta untuk pindah pin lokasi kantor</small>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-cog me-2 text-primary"></i>Konfigurasi</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.pengaturan-kantor.simpan') }}" id="formKantor">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Kantor</label>
                            <input type="text" name="nama_kantor"
                                   value="{{ old('nama_kantor', $kantor->nama_kantor ?? 'Kantor Pusat') }}"
                                   class="form-control" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold">Latitude</label>
                                <input type="text" name="latitude" id="inputLat"
                                       value="{{ old('latitude', $kantor->latitude) }}"
                                       class="form-control" placeholder="-6.200000" readonly>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold">Longitude</label>
                                <input type="text" name="longitude" id="inputLng"
                                       value="{{ old('longitude', $kantor->longitude) }}"
                                       class="form-control" placeholder="106.800000" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">
                                Radius Area Absensi: <span id="radiusLabel" class="text-primary fw-bold">{{ $kantor->radius_meter ?? 100 }}m</span>
                            </label>
                            <input type="range" name="radius_meter" id="radiusSlider"
                                   min="10" max="1000" step="10"
                                   value="{{ old('radius_meter', $kantor->radius_meter ?? 100) }}"
                                   class="form-range">
                            <div class="d-flex justify-content-between text-muted small">
                                <span>10m</span><span>500m</span><span>1000m</span>
                            </div>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Mulai Masuk</label>
                                <input type="time" name="jam_masuk_mulai"
                                       value="{{ old('jam_masuk_mulai', $kantor->jam_masuk_mulai ?? '07:00') }}"
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Batas Terlambat</label>
                                <input type="time" name="jam_masuk_batas"
                                       value="{{ old('jam_masuk_batas', $kantor->jam_masuk_batas ?? '09:00') }}"
                                       class="form-control form-control-sm">
                            </div>
                            <div class="col-4">
                                <label class="form-label fw-semibold small">Min. Keluar</label>
                                <input type="time" name="jam_keluar_minimal"
                                       value="{{ old('jam_keluar_minimal', $kantor->jam_keluar_minimal ?? '16:00') }}"
                                       class="form-control form-control-sm">
                            </div>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="wajib_lokasi"
                                   id="wajibLokasi" value="1"
                                   {{ ($kantor->wajib_lokasi ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="wajibLokasi">
                                <strong>Wajib dalam radius</strong>
                                <small class="text-muted d-block">Jika dinonaktifkan, karyawan bisa absen dari mana saja</small>
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                            <i class="fas fa-save me-2"></i>Simpan Pengaturan
                        </button>
                    </form>
                </div>
            </div>

            @if($kantor->latitude && $kantor->longitude)
            <div class="card mt-3">
                <div class="card-body py-2">
                    <p class="small mb-1 fw-semibold"><i class="fas fa-link me-1 text-primary"></i>Link Absensi GPS Karyawan</p>
                    <div class="input-group input-group-sm">
                        <input type="text" class="form-control" id="linkAbsensi"
                               value="{{ url('/karyawan/absensi') }}" readonly>
                        <button class="btn btn-outline-secondary" onclick="copyLink()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted">Share link ini ke semua karyawan</small>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // ── Init Peta ──────────────────────────────────────────────
    const defaultLat = {{ $kantor->latitude ?? -6.2 }};
    const defaultLng = {{ $kantor->longitude ?? 106.8 }};
    const radius     = {{ $kantor->radius_meter ?? 100 }};

    const map = L.map('peta-admin').setView([defaultLat, defaultLng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap'
    }).addTo(map);

    const icon = L.divIcon({
        html: '<div style="background:#dc2626;width:22px;height:22px;border-radius:50%;border:3px solid #fff;box-shadow:0 3px 10px rgba(0,0,0,0.3)"></div>',
        iconSize:[22,22], iconAnchor:[11,11], className:''
    });

    let marker   = L.marker([defaultLat, defaultLng], {icon, draggable:true}).addTo(map);
    let lingkaran = L.circle([defaultLat, defaultLng], {
        radius, color:'#16a34a', fillColor:'#16a34a', fillOpacity:0.1, weight:2, dashArray:'6,4'
    }).addTo(map);

    function updateKoordinat(lat, lng) {
        document.getElementById('inputLat').value = lat.toFixed(7);
        document.getElementById('inputLng').value = lng.toFixed(7);
        marker.setLatLng([lat, lng]);
        lingkaran.setLatLng([lat, lng]);
    }

    // Drag marker
    marker.on('dragend', e => {
        const p = e.target.getLatLng();
        updateKoordinat(p.lat, p.lng);
    });

    // Klik peta
    map.on('click', e => {
        updateKoordinat(e.latlng.lat, e.latlng.lng);
        map.setView(e.latlng, map.getZoom());
    });

    // Slider radius
    document.getElementById('radiusSlider').addEventListener('input', function() {
        document.getElementById('radiusLabel').textContent = this.value + 'm';
        lingkaran.setRadius(parseInt(this.value));
    });

    // Deteksi lokasi admin
    document.getElementById('btnDeteksiKantor').addEventListener('click', () => {
        navigator.geolocation.getCurrentPosition(pos => {
            updateKoordinat(pos.coords.latitude, pos.coords.longitude);
            map.setView([pos.coords.latitude, pos.coords.longitude], 17);
        }, () => alert('Gagal deteksi lokasi.'), {enableHighAccuracy:true});
    });

    function copyLink() {
        navigator.clipboard.writeText(document.getElementById('linkAbsensi').value)
            .then(() => alert('Link disalin!'));
    }
</script>
@endpush
