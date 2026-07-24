@extends('layouts.app')

@section('title', 'QR Code – ' . $karyawan->name)

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h5 class="fw-bold mb-0">QR Code – {{ $karyawan->name }}</h5>
    </div>

    <div class="row g-4">
        <div class="col-md-5">
            <div class="card text-center">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-1">{{ $karyawan->name }}</h6>
                    <p class="text-muted small mb-3">{{ $karyawan->jabatan ?? '' }} {{ $karyawan->divisi ? '– '.$karyawan->divisi : '' }}</p>

                    @if($karyawan->qr_token)
                        <div class="border rounded p-3 d-inline-block mb-3" id="qr-container">
                            {!! QrCode::size(220)->generate(route('absensi.scan', $karyawan->qr_token)) !!}
                        </div>
                        <p class="text-muted small mb-3">Scan QR ini dari HP untuk absensi</p>

                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <button onclick="printQr()" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-print me-1"></i>Print
                            </button>
                            <form method="POST" action="{{ route('admin.karyawan.regenerate-qr', $karyawan) }}"
                                  onsubmit="return confirm('Regenerate QR akan membuat QR lama tidak valid. Lanjutkan?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-warning btn-sm">
                                    <i class="fas fa-sync me-1"></i>Regenerate QR
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-danger">QR token tidak ditemukan.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Informasi</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm">
                        <tr><td class="text-muted" width="40%">Nama</td><td class="fw-semibold">{{ $karyawan->name }}</td></tr>
                        <tr><td class="text-muted">NIP</td><td>{{ $karyawan->nip ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Email</td><td>{{ $karyawan->email }}</td></tr>
                        <tr><td class="text-muted">Jabatan</td><td>{{ $karyawan->jabatan ?? '-' }}</td></tr>
                        <tr><td class="text-muted">Divisi</td><td>{{ $karyawan->divisi ?? '-' }}</td></tr>
                        <tr>
                            <td class="text-muted">URL Absensi</td>
                            <td>
                                <small class="text-break text-primary">
                                    {{ route('absensi.scan', $karyawan->qr_token) }}
                                </small>
                            </td>
                        </tr>
                    </table>

                    <div class="alert alert-info mt-3 mb-0 small">
                        <i class="fas fa-lightbulb me-2"></i>
                        <strong>Cara penggunaan:</strong> Karyawan cukup scan QR code ini dari kamera HP.
                        Akan langsung terbuka halaman absensi tanpa perlu login.
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function printQr() {
    const qr = document.getElementById('qr-container').innerHTML;
    const w = window.open('', '_blank');
    w.document.write(`
        <html><head><title>QR - {{ $karyawan->name }}</title>
        <style>body{text-align:center;font-family:sans-serif;padding:30px}h2{margin-bottom:5px}p{color:#666}</style>
        </head><body>
        <h2>{{ $karyawan->name }}</h2>
        <p>{{ $karyawan->jabatan ?? '' }} {{ $karyawan->divisi ? '| '.$karyawan->divisi : '' }}</p>
        ${qr}
        <p style="margin-top:10px;font-size:12px">Scan untuk absensi</p>
        <script>window.onload=()=>window.print()<\/script>
        </body></html>
    `);
    w.document.close();
}
</script>
@endpush
