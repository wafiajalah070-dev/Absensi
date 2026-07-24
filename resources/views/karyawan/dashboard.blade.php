@extends('layouts.app')

@section('title', 'Dashboard Karyawan')

@section('sidebar-menu')
    @include('layouts.karyawan-sidebar')
@endsection

@section('content')
    <h5 class="fw-bold mb-0">Halo, {{ $user->name }} 👋</h5>
    <p class="text-muted small mb-4">{{ now()->translatedFormat('l, d F Y') }}</p>

    {{-- ── Status Absensi Hari Ini ── --}}
    <div class="card mb-4" style="border-left: 4px solid
        {{ $absensiHari ? ($absensiHari->jam_keluar ? '#198754' : '#f39c12') : '#dc3545' }}">
        <div class="card-body">
            <div class="row align-items-center g-2">
                <div class="col">
                    <p class="text-muted small mb-1">Status Absensi Hari Ini</p>
                    @if(!$absensiHari)
                        <h6 class="fw-bold text-danger mb-0"><i class="fas fa-times-circle me-2"></i>Belum Absen</h6>
                    @elseif(!$absensiHari->jam_keluar)
                        <h6 class="fw-bold text-warning mb-0"><i class="fas fa-sign-in-alt me-2"></i>Sudah Masuk Pukul {{ \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i') }}</h6>
                        @if($absensiHari->keterangan === 'Terlambat')
                            <span class="badge bg-warning text-dark mt-1">⚠️ Terlambat</span>
                        @endif
                    @else
                        <h6 class="fw-bold text-success mb-0"><i class="fas fa-check-circle me-2"></i>Absensi Lengkap</h6>
                        <small class="text-muted">
                            Masuk {{ \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i') }} ·
                            Keluar {{ \Carbon\Carbon::parse($absensiHari->jam_keluar)->format('H:i') }}
                        </small>
                    @endif
                </div>
                <div class="col-auto">
                    <a href="{{ route('karyawan.absensi') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-map-marker-alt me-1"></i>Absensi GPS
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Stat Cards Bulan Ini ── --}}
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center py-3 h-100">
                <div class="text-success fw-bold fs-3">{{ $bulanIni['hadir'] }}</div>
                <div class="text-muted small">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3 h-100">
                <div class="text-warning fw-bold fs-3">{{ $bulanIni['izin'] }}</div>
                <div class="text-muted small">Izin/Sakit</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3 h-100">
                <div class="text-danger fw-bold fs-3">{{ $bulanIni['alpha'] }}</div>
                <div class="text-muted small">Alpha</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3 h-100">
                <div class="text-primary fw-bold fs-3">{{ $persenHadir }}%</div>
                <div class="text-muted small">Kehadiran</div>
            </div>
        </div>
    </div>

    {{-- ── Progress Kehadiran ── --}}
    <div class="card mb-4">
        <div class="card-body py-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-semibold small">Tingkat Kehadiran Bulan {{ now()->translatedFormat('F') }}</span>
                <span class="fw-bold text-primary">{{ $bulanIni['hadir'] }} / {{ $hariKerja }} hari kerja</span>
            </div>
            <div class="progress" style="height:10px;border-radius:10px">
                <div class="progress-bar {{ $persenHadir >= 80 ? 'bg-success' : ($persenHadir >= 60 ? 'bg-warning' : 'bg-danger') }}"
                     style="width:{{ $persenHadir }}%;border-radius:10px">
                </div>
            </div>
            <div class="d-flex justify-content-between mt-1">
                <small class="text-muted">0%</small>
                <small class="text-muted fw-semibold {{ $persenHadir >= 80 ? 'text-success' : 'text-warning' }}">{{ $persenHadir }}%</small>
                <small class="text-muted">100%</small>
            </div>
        </div>
    </div>

    {{-- ── Grafik & Riwayat ── --}}
    <div class="row g-3 mb-4">
        {{-- Grafik Mingguan --}}
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Kehadiran 4 Minggu Terakhir</h6>
                </div>
                <div class="card-body pt-0">
                    <canvas id="grafikKaryawan" height="160"></canvas>
                </div>
            </div>
        </div>

        {{-- Info Pribadi --}}
        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-id-card me-2 text-primary"></i>Profil Saya</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm small">
                        <tr><td class="text-muted" width="40%">Nama</td><td class="fw-semibold">{{ $user->name }}</td></tr>
                        <tr><td class="text-muted">NIP</td><td>{{ $user->nip ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Jabatan</td><td>{{ $user->jabatan ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Divisi</td><td>{{ $user->divisi ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Email</td><td class="text-break">{{ $user->email }}</td></tr>
                        <tr>
                            <td class="text-muted">Status Email</td>
                            <td>
                                @if($user->hasVerifiedEmail())
                                    <span class="badge bg-success"><i class="fas fa-check me-1"></i>Terverifikasi</span>
                                @else
                                    <span class="badge bg-warning text-dark">Belum Verifikasi</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Riwayat 7 hari ── --}}
    <div class="card">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between">
            <h6 class="fw-bold mb-0"><i class="fas fa-history me-2 text-primary"></i>7 Hari Terakhir</h6>
            <a href="{{ route('karyawan.riwayat') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Tanggal</th>
                            <th>Masuk</th>
                            <th>Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riwayat as $r)
                            <tr>
                                <td class="ps-3">
                                    <div class="small fw-semibold">{{ $r->tanggal->format('d/m/Y') }}</div>
                                    <div class="text-muted" style="font-size:0.75rem">{{ $r->tanggal->translatedFormat('l') }}</div>
                                </td>
                                <td class="small">{{ $r->jam_masuk ? \Carbon\Carbon::parse($r->jam_masuk)->format('H:i') : '-' }}</td>
                                <td class="small">{{ $r->jam_keluar ? \Carbon\Carbon::parse($r->jam_keluar)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($r->status === 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                        @if($r->keterangan === 'Terlambat')
                                            <span class="badge bg-warning text-dark">Telat</span>
                                        @endif
                                    @elseif(in_array($r->status, ['izin','sakit']))
                                        <span class="badge bg-warning text-dark">{{ ucfirst($r->status) }}</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3 small">Belum ada riwayat</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('grafikKaryawan'), {
    type: 'line',
    data: {
        labels: @json($grafikMingguan->pluck('label')),
        datasets: [
            {
                label: 'Hadir',
                data: @json($grafikMingguan->pluck('hadir')),
                borderColor: '#198754',
                backgroundColor: 'rgba(25,135,84,0.1)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#198754',
            },
            {
                label: 'Alpha',
                data: @json($grafikMingguan->pluck('alpha')),
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220,53,69,0.08)',
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: '#dc3545',
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 12 } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#f0f0f0' } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
