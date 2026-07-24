<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Tahunan {{ $tahun }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'DejaVu Sans',sans-serif; font-size:8px; color:#222; }
        .header { background:#1e3a5f; color:#fff; padding:10px 14px; margin-bottom:10px; }
        .header h1 { font-size:13px; margin-bottom:2px; }
        .header p  { font-size:9px; opacity:0.85; }
        table { width:100%; border-collapse:collapse; font-size:7.5px; }
        th { background:#1e3a5f; color:#fff; padding:4px 3px; text-align:center; border:1px solid #2d6a9f; }
        td { padding:3px; border:1px solid #ddd; text-align:center; }
        tr:nth-child(even) td { background:#f9f9f9; }
        .td-left { text-align:left; }
        .hadir { color:#155724; font-weight:bold; }
        .izin  { color:#856404; }
        .alpha { color:#721c24; }
        tfoot td { background:#eef2f7 !important; font-weight:bold; }
        .footer { margin-top:10px; font-size:8px; color:#999; text-align:right; }
        .badge-ok   { background:#d4edda; color:#155724; padding:1px 4px; border-radius:3px; }
        .badge-warn { background:#fff3cd; color:#856404; padding:1px 4px; border-radius:3px; }
        .badge-bad  { background:#f8d7da; color:#721c24; padding:1px 4px; border-radius:3px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rekap Absensi Tahunan {{ $tahun }}</h1>
        <p>
            {{ $divisi ? 'Divisi: '.$divisi : 'Semua Divisi' }} ·
            Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2">#</th>
                <th rowspan="2">Nama</th>
                <th rowspan="2">Divisi</th>
                @foreach(['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'] as $b)
                    <th colspan="3">{{ $b }}</th>
                @endforeach
                <th rowspan="2">∑H</th>
                <th rowspan="2">∑I</th>
                <th rowspan="2">∑A</th>
                <th rowspan="2">%</th>
            </tr>
            <tr>
                @for($m=1;$m<=12;$m++)
                    <th>H</th><th>I</th><th>A</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($data as $i => $row)
                @php
                    $k = $row['k'];
                    $hk = 0;
                    for($m=1;$m<=12;$m++){
                        $tgl=\Carbon\Carbon::create($tahun,$m,1);
                        $akhir=$tgl->copy()->endOfMonth();
                        $h=$tgl->copy();
                        while($h->lte($akhir)){
                            if(!$h->isWeekend()&&$h->lte(now()))$hk++;
                            $h->addDay();
                        }
                    }
                    $pct=$hk>0?round($row['totalH']/$hk*100):0;
                @endphp
                <tr>
                    <td>{{ $i+1 }}</td>
                    <td class="td-left">{{ $k->name }}</td>
                    <td>{{ $k->divisi??'-' }}</td>
                    @for($m=1;$m<=12;$m++)
                        @php $rb=$row['rekapBulan'][$m]; @endphp
                        <td class="hadir">{{ $rb['h']?:'-' }}</td>
                        <td class="izin">{{ $rb['i']?:'-' }}</td>
                        <td class="alpha">{{ $rb['a']?:'-' }}</td>
                    @endfor
                    <td class="hadir">{{ $row['totalH'] }}</td>
                    <td class="izin">{{ $row['totalI'] }}</td>
                    <td class="alpha">{{ $row['totalA'] }}</td>
                    <td>
                        <span class="{{ $pct>=80?'badge-ok':($pct>=60?'badge-warn':'badge-bad') }}">{{ $pct }}%</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right">Total</td>
                @for($m=1;$m<=12;$m++)
                    <td class="hadir">{{ collect($data)->sum(fn($r)=>$r['rekapBulan'][$m]['h']) }}</td>
                    <td class="izin">{{ collect($data)->sum(fn($r)=>$r['rekapBulan'][$m]['i']) }}</td>
                    <td class="alpha">{{ collect($data)->sum(fn($r)=>$r['rekapBulan'][$m]['a']) }}</td>
                @endfor
                <td class="hadir">{{ collect($data)->sum('totalH') }}</td>
                <td class="izin">{{ collect($data)->sum('totalI') }}</td>
                <td class="alpha">{{ collect($data)->sum('totalA') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Laporan dibuat oleh Sistem AbsensiKP · {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
