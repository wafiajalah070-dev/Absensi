<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi {{ \Carbon\Carbon::create(null, $bulan)->translatedFormat('F') }} {{ $tahun }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #222; }
        .header { background: #1e3a5f; color: #fff; padding: 14px 16px; margin-bottom: 14px; }
        .header h1 { font-size: 15px; margin-bottom: 3px; }
        .header p  { font-size: 10px; opacity: 0.85; }
        .info-box { display: flex; gap: 16px; margin-bottom: 12px; padding: 0 2px; }
        .info-item { background: #f4f6f9; border-radius: 6px; padding: 8px 12px; flex: 1; text-align: center; }
        .info-item .val { font-size: 18px; font-weight: 700; color: #1e3a5f; }
        .info-item .lbl { font-size: 9px; color: #666; }
        table { width: 100%; border-collapse: collapse; font-size: 9px; }
        th { background: #1e3a5f; color: #fff; padding: 6px 5px; text-align: center; }
        td { padding: 5px 5px; border-bottom: 1px solid #eee; }
        tr:nth-child(even) td { background: #f9f9f9; }
        .badge-hadir  { background: #d4edda; color: #155724; padding: 2px 6px; border-radius: 4px; }
        .badge-izin   { background: #fff3cd; color: #856404; padding: 2px 6px; border-radius: 4px; }
        .badge-sakit  { background: #d1ecf1; color: #0c5460; padding: 2px 6px; border-radius: 4px; }
        .badge-alpha  { background: #f8d7da; color: #721c24; padding: 2px 6px; border-radius: 4px; }
        .footer { margin-top: 14px; font-size: 9px; color: #999; text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Absensi Karyawan</h1>
        <p>
            Periode: {{ \Carbon\Carbon::create(null, $bulan)->translatedFormat('F') }} {{ $tahun }}
            @if(isset($karyawan)) · {{ $karyawan->name }} ({{ $karyawan->nip ?? '-' }}) @endif
        </p>
        <p>Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    {{-- Ringkasan --}}
    @php
        $totalHadir = $absensis->where('status','hadir')->count();
        $totalIzin  = $absensis->whereIn('status',['izin','sakit'])->count();
        $totalAlpha = $absensis->where('status','alpha')->count();
        $totalData  = $absensis->count();
    @endphp
    <table style="width:100%;margin-bottom:12px;border-collapse:collapse">
        <tr>
            <td style="width:25%;background:#d4edda;padding:8px;text-align:center;border-radius:4px">
                <div style="font-size:18px;font-weight:700;color:#155724">{{ $totalHadir }}</div>
                <div style="font-size:9px;color:#155724">Hadir</div>
            </td>
            <td style="width:5%"></td>
            <td style="width:25%;background:#fff3cd;padding:8px;text-align:center;border-radius:4px">
                <div style="font-size:18px;font-weight:700;color:#856404">{{ $totalIzin }}</div>
                <div style="font-size:9px;color:#856404">Izin/Sakit</div>
            </td>
            <td style="width:5%"></td>
            <td style="width:25%;background:#f8d7da;padding:8px;text-align:center;border-radius:4px">
                <div style="font-size:18px;font-weight:700;color:#721c24">{{ $totalAlpha }}</div>
                <div style="font-size:9px;color:#721c24">Alpha</div>
            </td>
            <td style="width:5%"></td>
            <td style="width:10%;background:#e2e3e5;padding:8px;text-align:center;border-radius:4px">
                <div style="font-size:18px;font-weight:700;color:#383d41">{{ $totalData }}</div>
                <div style="font-size:9px;color:#383d41">Total</div>
            </td>
        </tr>
    </table>

    {{-- Tabel data --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                @if(!isset($karyawan))<th>Nama Karyawan</th><th>Divisi</th>@endif
                <th>Tanggal</th>
                <th>Hari</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensis as $i => $a)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    @if(!isset($karyawan))
                        <td class="fw-bold">{{ $a->user->name }}</td>
                        <td>{{ $a->user->divisi ?? '-' }}</td>
                    @endif
                    <td class="text-center">{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($a->tanggal)->translatedFormat('D') }}</td>
                    <td class="text-center">{{ $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : '-' }}</td>
                    <td class="text-center">{{ $a->jam_keluar ? \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') : '-' }}</td>
                    <td class="text-center">
                        @if($a->jam_masuk && $a->jam_keluar)
                            @php $mnt = \Carbon\Carbon::parse($a->jam_masuk)->diffInMinutes(\Carbon\Carbon::parse($a->jam_keluar)); @endphp
                            {{ round($mnt/60,1) }}j
                        @else - @endif
                    </td>
                    <td class="text-center">
                        @if($a->status === 'hadir')
                            <span class="badge-hadir">Hadir</span>
                        @elseif($a->status === 'izin')
                            <span class="badge-izin">Izin</span>
                        @elseif($a->status === 'sakit')
                            <span class="badge-sakit">Sakit</span>
                        @else
                            <span class="badge-alpha">Alpha</span>
                        @endif
                    </td>
                    <td>{{ $a->keterangan ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="10" class="text-center" style="padding:16px;color:#999">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Laporan dibuat otomatis oleh Sistem AbsensiKP · {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
