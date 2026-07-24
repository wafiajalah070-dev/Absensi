@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h5 class="fw-bold mb-0">Rekap Absensi</h5>
        <p class="text-muted small mb-0">
            {{ \Carbon\Carbon::create(null, $bulan)->translatedFormat('F') }} {{ $tahun }} ·
            <span class="fw-semibold">{{ $jumlahHariKerja }} hari kerja</span>
        </p>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.rekap.tahunan', ['tahun' => $tahun]) }}"
               class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-check me-1"></i>Rekap Tahunan
            </a>
            <a href="{{ route('admin.export.excel', ['bulan' => $bulan, 'tahun' => $tahun, 'divisi' => $divisi]) }}"
               class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
            <a href="{{ route('admin.export.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
            </a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Bulan</label>
                    <select name="bulan" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @for($y = now()->year; $y >= now()->year - 3; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Divisi</label>
                    <select name="divisi" class="form-select form-select-sm">
                        <option value="">Semua</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label small mb-1">Cari</label>
                    <input type="text" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Nama / NIP">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="{{ route('admin.rekap.index') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Karyawan</th>
                            <th>Divisi</th>
                            <th class="text-center text-success">Hadir</th>
                            <th class="text-center text-warning">Izin</th>
                            <th class="text-center text-info">Sakit</th>
                            <th class="text-center text-danger">Alpha</th>
                            <th class="text-center">% Hadir</th>
                            <th class="text-center">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $k)
                            @php
                                $hadir  = $k->absensis->where('status', 'hadir')->count();
                                $izin   = $k->absensis->where('status', 'izin')->count();
                                $sakit  = $k->absensis->where('status', 'sakit')->count();
                                $absensiAda = $k->absensis->count();
                                // Alpha = hari kerja - semua record absensi (termasuk izin/sakit yg disetujui)
                                $alpha  = max(0, $jumlahHariKerja - $absensiAda);
                                $telat  = $k->absensis->where('keterangan', 'Terlambat')->count();
                                $persen = $jumlahHariKerja > 0 ? round($hadir / $jumlahHariKerja * 100) : 0;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-semibold">{{ $k->name }}</div>
                                    <div class="text-muted small">{{ $k->nip ?? '-' }}</div>
                                </td>
                                <td>{{ $k->divisi ?? '-' }}</td>
                                <td class="text-center"><span class="badge bg-success">{{ $hadir }}</span></td>
                                <td class="text-center"><span class="badge bg-warning text-dark">{{ $izin }}</span></td>
                                <td class="text-center"><span class="badge bg-info">{{ $sakit }}</span></td>
                                <td class="text-center"><span class="badge bg-danger">{{ $alpha }}</span></td>
                                <td class="text-center">
                                    <span class="badge {{ $persen>=80?'bg-success':($persen>=60?'bg-warning text-dark':'bg-danger') }}">
                                        {{ $persen }}%
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.rekap.detail', [$k, 'bulan' => $bulan, 'tahun' => $tahun]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Tidak ada data</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($karyawans->hasPages())
            <div class="card-footer bg-white">
                {{ $karyawans->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection
