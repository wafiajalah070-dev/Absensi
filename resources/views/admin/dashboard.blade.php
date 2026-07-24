@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Dashboard</h5>
            <p class="text-muted small mb-0">{{ now()->translatedFormat('l, d F Y') }} · WIB</p>
        </div>
        @if($pendingIzin > 0)
            <a href="{{ route('admin.izin.index') }}" class="btn btn-warning btn-sm">
                <i class="fas fa-bell me-1"></i>{{ $pendingIzin }} izin menunggu
            </a>
        @endif
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card h-100" style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Total Karyawan</p>
                        <h2 class="mb-0 fw-bold">{{ $totalKaryawan }}</h2>
                    </div>
                    <div class="opacity-50 fs-2"><i class="fas fa-users"></i></div>
                </div>
                <p class="mb-0 mt-2 small opacity-75">Terdaftar di sistem</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card h-100" style="background:linear-gradient(135deg,#198754,#20c997)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Hadir Hari Ini</p>
                        <h2 class="mb-0 fw-bold">{{ $hadirHariIni }}</h2>
                    </div>
                    <div class="opacity-50 fs-2"><i class="fas fa-check-circle"></i></div>
                </div>
                <p class="mb-0 mt-2 small opacity-75">dari {{ $totalKaryawan }} karyawan</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card h-100" style="background:linear-gradient(135deg,#e67e22,#f39c12)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Izin / Sakit</p>
                        <h2 class="mb-0 fw-bold">{{ $izinHariIni }}</h2>
                    </div>
                    <div class="opacity-50 fs-2"><i class="fas fa-calendar-minus"></i></div>
                </div>
                <p class="mb-0 mt-2 small opacity-75">Hari ini</p>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card h-100" style="background:linear-gradient(135deg,#dc3545,#c0392b)">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="mb-1 opacity-75 small">Tidak Hadir</p>
                        <h2 class="mb-0 fw-bold">{{ $alphaHariIni }}</h2>
                    </div>
                    <div class="opacity-50 fs-2"><i class="fas fa-times-circle"></i></div>
                </div>
                <p class="mb-0 mt-2 small opacity-75">Alpha hari ini</p>
            </div>
        </div>
    </div>

    {{-- ── Grafik ── --}}
    <div class="row g-3 mb-4">
        {{-- Bar Chart 7 hari --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Absensi 7 Hari Terakhir</h6>
                </div>
                <div class="card-body pt-0">
                    <canvas id="grafikMingguan" height="120"></canvas>
                </div>
            </div>
        </div>
        {{-- Pie Chart bulan ini --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Bulan {{ now()->translatedFormat('F') }}</h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center pt-0">
                    <canvas id="grafikPie" style="max-height:180px"></canvas>
                    <div class="d-flex gap-3 mt-3 flex-wrap justify-content-center">
                        <div class="text-center">
                            <div class="fw-bold text-success fs-5">{{ $bulanIni['hadir'] }}</div>
                            <div class="text-muted small">Hadir</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-warning fs-5">{{ $bulanIni['izin'] }}</div>
                            <div class="text-muted small">Izin/Sakit</div>
                        </div>
                        <div class="text-center">
                            <div class="fw-bold text-danger fs-5">{{ $bulanIni['alpha'] }}</div>
                            <div class="text-muted small">Alpha</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Tabel bawah ── --}}
    <div class="row g-3">
        {{-- Absensi terbaru --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-clock me-2 text-primary"></i>Absensi Terbaru Hari Ini</h6>
                    <a href="{{ route('admin.rekap.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Karyawan</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($absensiTerbaru as $a)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold small">{{ $a->user->name }}</div>
                                            <div class="text-muted" style="font-size:0.75rem">{{ $a->user->divisi ?? '-' }}</div>
                                        </td>
                                        <td class="small">{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                        <td class="small">{{ $a->jam_keluar ? \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') : '-' }}</td>
                                        <td>
                                            @if($a->status === 'hadir')
                                                <span class="badge bg-success">Hadir</span>
                                                @if($a->keterangan === 'Terlambat')
                                                    <span class="badge bg-warning text-dark ms-1">Telat</span>
                                                @endif
                                            @elseif(in_array($a->status, ['izin','sakit']))
                                                <span class="badge bg-warning text-dark">{{ ucfirst($a->status) }}</span>
                                            @else
                                                <span class="badge bg-danger">Alpha</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                                {{-- Karyawan yang belum absen hari ini --}}
                                @foreach($karyawanAlpha as $a)
                                    <tr style="background:rgba(220,53,69,0.05)">
                                        <td class="ps-3">
                                            <div class="fw-semibold small">{{ $a->user->name }}</div>
                                            <div class="text-muted" style="font-size:0.75rem">{{ $a->user->divisi ?? '-' }}</div>
                                        </td>
                                        <td class="small text-muted">-</td>
                                        <td class="small text-muted">-</td>
                                        <td><span class="badge bg-danger">Alpha</span></td>
                                    </tr>
                                @endforeach
                                @if($absensiTerbaru->isEmpty() && $karyawanAlpha->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4 small">
                                            <i class="fas fa-inbox d-block mb-1 opacity-25 fs-4"></i>
                                            Belum ada absensi hari ini
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top alpha --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Sering Alpha Bulan Ini</h6>
                </div>
                <div class="card-body p-0">
                    @forelse($topAlpha->where('jumlah_alpha','>',0) as $k)
                        <div class="d-flex align-items-center px-3 py-2 border-bottom">
                            <div class="rounded-circle bg-danger text-white d-flex align-items-center justify-content-center me-3 flex-shrink-0"
                                 style="width:36px;height:36px;font-size:0.85rem;font-weight:700">
                                {{ strtoupper(substr($k->name,0,1)) }}
                            </div>
                            <div class="flex-grow-1 min-width-0">
                                <div class="fw-semibold small text-truncate">{{ $k->name }}</div>
                                <div class="text-muted" style="font-size:0.75rem">{{ $k->divisi ?? '-' }}</div>
                            </div>
                            <span class="badge bg-danger ms-2">{{ $k->jumlah_alpha }}x</span>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 small">
                            <i class="fas fa-thumbs-up d-block mb-1 opacity-25 fs-4"></i>
                            Tidak ada alpha bulan ini 🎉
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
// Data dari Laravel
const labels  = @json($grafik7Hari->pluck('label'));
const hadir   = @json($grafik7Hari->pluck('hadir'));
const izin    = @json($grafik7Hari->pluck('izin'));
const alpha   = @json($grafik7Hari->pluck('alpha'));

// ── Bar Chart 7 hari ──────────────────────────────────
new Chart(document.getElementById('grafikMingguan'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            { label: 'Hadir',     data: hadir, backgroundColor: '#198754', borderRadius: 6 },
            { label: 'Izin/Sakit',data: izin,  backgroundColor: '#f39c12', borderRadius: 6 },
            { label: 'Alpha',     data: alpha, backgroundColor: '#dc3545', borderRadius: 6 },
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 }, padding: 16 } },
            tooltip: { mode: 'index', intersect: false }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#f0f0f0' } }
        }
    }
});

// ── Pie Chart bulan ini ───────────────────────────────
const totalPie = {{ $bulanIni['hadir'] + $bulanIni['izin'] + $bulanIni['alpha'] }};
new Chart(document.getElementById('grafikPie'), {
    type: 'doughnut',
    data: {
        labels: ['Hadir', 'Izin/Sakit', 'Alpha'],
        datasets: [{
            data: [{{ $bulanIni['hadir'] }}, {{ $bulanIni['izin'] }}, {{ $bulanIni['alpha'] }}],
            backgroundColor: ['#198754', '#f39c12', '#dc3545'],
            borderWidth: 2,
            borderColor: '#fff',
            hoverOffset: 6
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const pct = totalPie > 0 ? Math.round(ctx.raw / totalPie * 100) : 0;
                        return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                    }
                }
            }
        }
    }
});
</script>
@endpush
