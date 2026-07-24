@extends('layouts.app')

@section('title', 'Detail Rekap – ' . $karyawan->name)

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@section('content')
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('admin.rekap.index', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
           class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h5 class="fw-bold mb-0">{{ $karyawan->name }}</h5>
            <p class="text-muted small mb-0">
                Rekap {{ \Carbon\Carbon::create(null, $bulan)->translatedFormat('F') }} {{ $tahun }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.export.excel', ['bulan' => $bulan, 'tahun' => $tahun, 'user_id' => $karyawan->id]) }}"
               class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.export.pdf', ['bulan' => $bulan, 'tahun' => $tahun, 'user_id' => $karyawan->id]) }}"
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
        </div>
    </div>

    @php
        $hadir = $absensis->where('status', 'hadir')->count();
        $izin  = $absensis->where('status', 'izin')->count();
        $sakit = $absensis->where('status', 'sakit')->count();
        $alpha = $jumlahHari - $absensis->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <div class="text-success fw-bold fs-3">{{ $hadir }}</div>
                <div class="text-muted small">Hadir</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <div class="text-warning fw-bold fs-3">{{ $izin }}</div>
                <div class="text-muted small">Izin</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <div class="text-info fw-bold fs-3">{{ $sakit }}</div>
                <div class="text-muted small">Sakit</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-center py-3">
                <div class="text-danger fw-bold fs-3">{{ max(0, $alpha) }}</div>
                <div class="text-muted small">Alpha</div>
            </div>
        </div>
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
                            <th>Durasi</th>
                            <th>Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($d = 1; $d <= $jumlahHari; $d++)
                            @php
                                $tgl     = \Carbon\Carbon::create($tahun, $bulan, $d);
                                $absensi = $absensis->first(fn($a) => $a->tanggal->day === $d);
                            @endphp
                            <tr class="{{ $tgl->isWeekend() ? 'table-light' : '' }}">
                                <td class="ps-4">{{ $tgl->format('d/m/Y') }}</td>
                                <td class="text-muted small">{{ $tgl->translatedFormat('l') }}</td>
                                <td>{{ $absensi?->jam_masuk ? \Carbon\Carbon::parse($absensi->jam_masuk)->format('H:i') : '-' }}</td>
                                <td>{{ $absensi?->jam_keluar ? \Carbon\Carbon::parse($absensi->jam_keluar)->format('H:i') : '-' }}</td>
                                <td class="text-muted small">
                                    @if($absensi?->jam_masuk && $absensi?->jam_keluar)
                                        @php
                                            $durasi = \Carbon\Carbon::parse($absensi->jam_masuk)
                                                        ->diff(\Carbon\Carbon::parse($absensi->jam_keluar));
                                        @endphp
                                        {{ $durasi->h }}j {{ $durasi->i }}m
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    @if(!$absensi)
                                        @if($tgl->isWeekend())
                                            <span class="badge bg-secondary">Libur</span>
                                        @elseif($tgl->isFuture())
                                            <span class="badge bg-light text-muted border">–</span>
                                        @else
                                            <span class="badge bg-danger">Alpha</span>
                                        @endif
                                    @elseif($absensi->status === 'hadir')
                                        <span class="badge bg-success">Hadir</span>
                                    @elseif($absensi->status === 'izin')
                                        <span class="badge bg-warning text-dark">Izin</span>
                                    @elseif($absensi->status === 'sakit')
                                        <span class="badge bg-info">Sakit</span>
                                    @else
                                        <span class="badge bg-danger">Alpha</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $absensi?->keterangan ?? '-' }}</td>
                            </tr>
                        @endfor
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
