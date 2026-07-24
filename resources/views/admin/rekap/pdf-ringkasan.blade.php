<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi {{ \Carbon\Carbon::create(null,$bulan)->translatedFormat('F') }} {{ $tahun }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#222; }
        .header { background:#1e3a5f; color:#fff; padding:12px 16px; margin-bottom:12px; }
        .header h1 { font-size:14px; margin-bottom:3px; }
        .header p  { font-size:9px; opacity:0.85; }

        /* Stat boxes */
        .stat-row { display:table; width:100%; margin-bottom:12px; border-spacing:8px; }
        .stat-box { display:table-cell; background:#f4f6f9; border-radius:6px; padding:8px 10px; text-align:center; }
        .stat-box .val { font-size:20px; font-weight:700; }
        .stat-box .lbl { font-size:8px; color:#666; }
        .green { color:#155724; } .orange { color:#856404; } .red { color:#721c24; }

        table { width:100%; border-collapse:collapse; font-size:9px; }
        thead th { background:#1e3a5f; color:#fff; padding:5px 6px; text-align:center; border:1px solid #2d6a9f; }
        tbody td { padding:4px 6px; border:1px solid #e0e0e0; }
        tbody tr:nth-child(even) td { background:#f9f9f9; }
        .text-center { text-align:center; }
        .text-left   { text-align:left; }
        .fw-bold     { font-weight:bold; }
        .badge-ok   { background:#d4edda; color:#155724; padding:1px 5px; border-radius:3px; }
        .badge-warn { background:#fff3cd; color:#856404; padding:1px 5px; border-radius:3px; }
        .badge-bad  { background:#f8d7da; color:#721c24; padding:1px 5px; border-radius:3px; }
        tfoot td { background:#e9ecef; font-weight:bold; }
        .footer { margin-top:12px; font-size:8px; color:#999; text-align:right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Absensi — {{ \Carbon\Carbon::create(null,$bulan)->translatedFormat('F') }} {{ $tahun }}</h1>
        <p>Hari kerja: {{ $hariKerja }} hari &nbsp;|&nbsp; Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</p>
    </div>

    @php
        $totalHadir = collect($karyawans)->sum('hadir');
        $totalIzin  = collect($karyawans)->sum('izin');
        $totalAlpha = collect($karyawans)->sum('alpha');
    @endphp

    {{-- Ringkasan --}}
    <table style="margin-bottom:12px;border-collapse:separate;border-spacing:6px">
        <tr>
            <td style="background:#d4edda;padding:8px 12px;text-align:center;border-radius:4px;width:30%">
                <div class="val green fw-bold" style="font-size:18px">{{ $totalHadir }}</div>
                <div class="lbl">Total Hadir</div>
            </td>
            <td style="background:#fff3cd;padding:8px 12px;text-align:center;border-radius:4px;width:30%">
                <div class="val orange fw-bold" style="font-size:18px">{{ $totalIzin }}</div>
                <div class="lbl">Total Izin/Sakit</div>
            </td>
            <td style="background:#f8d7da;padding:8px 12px;text-align:center;border-radius:4px;width:30%">
                <div class="val red fw-bold" style="font-size:18px">{{ $totalAlpha }}</div>
                <div class="lbl">Total Alpha</div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>NIP</th>
                <th style="text-align:left">Nama Karyawan</th>
                <th>Divisi</th>
                <th>Jabatan</th>
                <th>Hadir</th>
                <th>Izin/Sakit</th>
                <th>Alpha</th>
                <th>Terlambat</th>
                <th>Hari Kerja</th>
                <th>% Kehadiran</th>
            </tr>
        </thead>
        <tbody>
            @foreach($karyawans as $i => $row)
                @php $pct = $row['persen']; @endphp
                <tr>
                    <td class="text-center">{{ $i+1 }}</td>
                    <td class="text-center">{{ $row['k']->nip ?? '-' }}</td>
                    <td class="text-left fw-bold">{{ $row['k']->name }}</td>
                    <td class="text-center">{{ $row['k']->divisi ?? '-' }}</td>
                    <td class="text-center">{{ $row['k']->jabatan ?? '-' }}</td>
                    <td class="text-center green fw-bold">{{ $row['hadir'] }}</td>
                    <td class="text-center orange">{{ $row['izin'] }}</td>
                    <td class="text-center red fw-bold">{{ $row['alpha'] }}</td>
                    <td class="text-center">{{ $row['telat'] }}</td>
                    <td class="text-center">{{ $hariKerja }}</td>
                    <td class="text-center">
                        <span class="{{ $pct>=80?'badge-ok':($pct>=60?'badge-warn':'badge-bad') }}">
                            {{ $pct }}%
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="text-left">Total</td>
                <td class="text-center green">{{ $totalHadir }}</td>
                <td class="text-center orange">{{ $totalIzin }}</td>
                <td class="text-center red">{{ $totalAlpha }}</td>
                <td class="text-center">{{ collect($karyawans)->sum('telat') }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Laporan dibuat otomatis oleh Sistem AbsensiKP &nbsp;·&nbsp; {{ now()->format('d/m/Y H:i') }} WIB
    </div>
</body>
</html>
