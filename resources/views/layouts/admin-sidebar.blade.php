{{-- Partial sidebar admin – include di setiap view admin --}}
@php $pendingIzin = \App\Models\Izin::where('status','pending')->count(); @endphp
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
</a>
<a href="{{ route('admin.scanner') }}" class="nav-link {{ request()->routeIs('admin.scanner*') ? 'active' : '' }}">
    <i class="fas fa-camera me-2"></i> Scanner Absensi
</a>
<a href="{{ route('admin.izin.index') }}" class="nav-link {{ request()->routeIs('admin.izin*') ? 'active' : '' }}">
    <i class="fas fa-file-alt me-2"></i> Kelola Izin
    @if($pendingIzin > 0)
        <span class="badge bg-danger ms-1">{{ $pendingIzin }}</span>
    @endif
</a>
<a href="{{ route('admin.karyawan.index') }}" class="nav-link {{ request()->routeIs('admin.karyawan.*') ? 'active' : '' }}">
    <i class="fas fa-users me-2"></i> Karyawan
</a>
<a href="{{ route('admin.rekap.index') }}" class="nav-link {{ request()->routeIs('admin.rekap.index') || request()->routeIs('admin.rekap.detail') ? 'active' : '' }}">
    <i class="fas fa-chart-bar me-2"></i> Rekap Bulanan
</a>
<a href="{{ route('admin.rekap.tahunan') }}" class="nav-link {{ request()->routeIs('admin.rekap.tahunan') ? 'active' : '' }}">
    <i class="fas fa-calendar-check me-2"></i> Rekap Tahunan
</a>
<a href="{{ route('admin.pengaturan-kantor') }}" class="nav-link {{ request()->routeIs('admin.pengaturan-kantor*') ? 'active' : '' }}">
    <i class="fas fa-map-marker-alt me-2"></i> Pengaturan Kantor
</a>
