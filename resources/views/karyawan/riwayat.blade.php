@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('sidebar-menu')
    @include('layouts.karyawan-sidebar')
@endsection

@section('content')
    <h5 class="fw-bold mb-4">Riwayat Absensi Saya</h5>

    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label small mb-1">Bulan</label>
                    <select name="bulan" class="form-select form-select-sm">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="form-label small mb-1">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @for($y = now()->year; $y >= now()->year - 2; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="pt-3">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>

    @php
        $totalHadir = $absensis->where('status','hadir')->count();
        $totalIzin  = $absensis->where('status','izin')->count();
        $totalSakit = $absensis->where('status','sakit')->count();
    @endphp
    <div class="row g-2 mb-3">
        <div class="col-4"><div class="card text-center py-2"><div class="text-success fw-bold">{{ $totalHadir }}</div><div class="text-muted small">Hadir</div></div></div>
        <div class="col-4"><div class="card text-center py-2"><div class="text-warning fw-bold">{{ $totalIzin }}</div><div class="text-muted small">Izin</div></div></div>
        <div class="col-4"><div class="card text-center py-2"><div class="text-info fw-bold">{{ $totalSakit }}</div><div class="text-muted small">Sakit</div></div></div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Tanggal</th>
                            <th>Hari</th>
                            <th>Jam Masuk</th>
                            <th>Jam Keluar</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensis as $a)
                            <tr>
                                <td class="ps-4">{{ $a->tanggal->format('d/m/Y') }}</td>
                                <td class="text-muted small">{{ $a->tanggal->translatedFormat('l') }}</td>
                                <td>{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $a->jam_keluar ? \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') : '-' }}</td>
                                <td>
                                    @if($a->status === 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($a->status === 'izin')
                                        <span class="badge bg-warning text-dark">Izin</span>
                                    @elseif($a->status === 'sakit')
                                        <span class="badge bg-info">Sakit</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Tidak ada data absensi bulan ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
