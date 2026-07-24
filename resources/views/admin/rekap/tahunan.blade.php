@extends('layouts.app')

@section('title', 'Rekap Tahunan')

@section('sidebar-menu')
    @include('layouts.admin-sidebar')
@endsection

@push('styles')
<style>
    .tbl-tahunan th, .tbl-tahunan td { font-size: 0.78rem; padding: 5px 6px; white-space: nowrap; }
    .tbl-tahunan .col-bulan { text-align: center; }
    .tbl-tahunan .hadir  { color: #198754; font-weight: 600; }
    .tbl-tahunan .izin   { color: #e67e22; }
    .tbl-tahunan .alpha  { color: #dc3545; }
    .tbl-tahunan tfoot td { background: #f4f6f9; font-weight: 700; }
    .legend span { display: inline-block; width: 10px; height: 10px; border-radius: 2px; margin-right: 4px; }
</style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h5 class="fw-bold mb-0">Rekap Tahunan</h5>
            <p class="text-muted small mb-0">Ringkasan kehadiran per bulan tahun {{ $tahun }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.export.excel-tahunan', ['tahun'=>$tahun,'divisi'=>$divisi]) }}"
               class="btn btn-success btn-sm">
                <i class="fas fa-file-excel me-1"></i>Excel
            </a>
            <a href="{{ route('admin.export.pdf-tahunan', ['tahun'=>$tahun,'divisi'=>$divisi]) }}"
               class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
            <a href="{{ route('admin.rekap.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-calendar-alt me-1"></i>Rekap Bulanan
            </a>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-3">
        <div class="card-body py-2">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small mb-1">Tahun</label>
                    <select name="tahun" class="form-select form-select-sm">
                        @for($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Divisi</label>
                    <select name="divisi" class="form-select form-select-sm">
                        <option value="">Semua Divisi</option>
                        @foreach($divisis as $d)
                            <option value="{{ $d }}" {{ $divisi == $d ? 'selected' : '' }}>{{ $d }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Cari</label>
                    <input type="text" name="search" value="{{ $search }}"
                           class="form-control form-control-sm" placeholder="Nama / NIP">
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="{{ route('admin.rekap.tahunan') }}" class="btn btn-sm btn-outline-secondary ms-1">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Legend --}}
    <div class="mb-2 legend small text-muted">
        <span style="background:#198754"></span>H = Hadir &nbsp;
        <span style="background:#e67e22"></span>I = Izin/Sakit &nbsp;
        <span style="background:#dc3545"></span>A = Alpha
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0 tbl-tahunan">
                    <thead class="table-dark">
                        <tr>
                            <th rowspan="2" class="align-middle">#</th>
                            <th rowspan="2" class="align-middle">Karyawan</th>
                            <th rowspan="2" class="align-middle">Divisi</th>
                            @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $bln)
                                <th colspan="3" class="text-center">{{ $bln }}</th>
                            @endforeach
                            <th rowspan="2" class="align-middle text-center text-success">∑H</th>
                            <th rowspan="2" class="align-middle text-center text-warning">∑I</th>
                            <th rowspan="2" class="align-middle text-center text-danger">∑A</th>
                            <th rowspan="2" class="align-middle text-center">%</th>
                        </tr>
                        <tr>
                            @for($m = 1; $m <= 12; $m++)
                                <th class="text-center text-success">H</th>
                                <th class="text-center text-warning">I</th>
                                <th class="text-center text-danger">A</th>
                            @endfor
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($data as $i => $row)
                            @php
                                $k = $row['karyawan'];
                                $hariKerja = 0;
                                for ($m = 1; $m <= 12; $m++) {
                                    $tgl = \Carbon\Carbon::create($tahun, $m, 1);
                                    $akhir = $tgl->copy()->endOfMonth();
                                    $h = $tgl->copy();
                                    while ($h->lte($akhir)) {
                                        if (!$h->isWeekend() && $h->lte(now())) $hariKerja++;
                                        $h->addDay();
                                    }
                                }
                                $persen = $hariKerja > 0 ? round($row['total_hadir'] / $hariKerja * 100) : 0;
                            @endphp
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $k->name }}</div>
                                    <div class="text-muted" style="font-size:0.72rem">{{ $k->nip ?? '-' }}</div>
                                </td>
                                <td>{{ $k->divisi ?? '-' }}</td>
                                @for($m = 1; $m <= 12; $m++)
                                    @php $rb = $row['rekap_bulan'][$m]; @endphp
                                    <td class="text-center hadir">{{ $rb['hadir'] ?: '-' }}</td>
                                    <td class="text-center izin">{{ $rb['izin'] ?: '-' }}</td>
                                    <td class="text-center alpha">{{ $rb['alpha'] ?: '-' }}</td>
                                @endfor
                                <td class="text-center fw-bold text-success">{{ $row['total_hadir'] }}</td>
                                <td class="text-center fw-bold text-warning">{{ $row['total_izin'] }}</td>
                                <td class="text-center fw-bold text-danger">{{ $row['total_alpha'] }}</td>
                                <td class="text-center">
                                    <span class="badge {{ $persen >= 80 ? 'bg-success' : ($persen >= 60 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                        {{ $persen }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="42" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block opacity-25"></i>
                                    Tidak ada data
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(count($data) > 0)
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Total</td>
                            @for($m = 1; $m <= 12; $m++)
                                <td class="text-center text-success fw-bold">
                                    {{ collect($data)->sum(fn($r) => $r['rekap_bulan'][$m]['hadir']) }}
                                </td>
                                <td class="text-center text-warning fw-bold">
                                    {{ collect($data)->sum(fn($r) => $r['rekap_bulan'][$m]['izin']) }}
                                </td>
                                <td class="text-center text-danger fw-bold">
                                    {{ collect($data)->sum(fn($r) => $r['rekap_bulan'][$m]['alpha']) }}
                                </td>
                            @endfor
                            <td class="text-center text-success fw-bold">{{ collect($data)->sum('total_hadir') }}</td>
                            <td class="text-center text-warning fw-bold">{{ collect($data)->sum('total_izin') }}</td>
                            <td class="text-center text-danger fw-bold">{{ collect($data)->sum('total_alpha') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
