<a href="{{ route('karyawan.dashboard') }}" class="nav-link {{ request()->routeIs('karyawan.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home me-2"></i> Dashboard
</a>
<a href="{{ route('karyawan.absensi') }}" class="nav-link {{ request()->routeIs('karyawan.absensi*') ? 'active' : '' }}">
    <i class="fas fa-map-marker-alt me-2"></i> Absensi GPS
</a>
<a href="{{ route('karyawan.izin.index') }}" class="nav-link {{ request()->routeIs('karyawan.izin*') ? 'active' : '' }}">
    <i class="fas fa-file-alt me-2"></i> Izin / Sakit
</a>
<a href="{{ route('karyawan.riwayat') }}" class="nav-link {{ request()->routeIs('karyawan.riwayat') ? 'active' : '' }}">
    <i class="fas fa-history me-2"></i> Riwayat Absensi
</a>
<a href="{{ route('karyawan.qr-saya') }}" class="nav-link" target="_blank">
    <i class="fas fa-qrcode me-2"></i> QR Saya
</a>
