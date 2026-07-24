<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapController extends Controller
{
    public function index(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);
        $divisi = $request->input('divisi');
        $search = $request->input('search');

        $karyawans = User::where('role', 'karyawan')
            ->when($divisi, fn($q) => $q->where('divisi', $divisi))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%"))
            ->with(['absensis' => function ($q) use ($bulan, $tahun) {
                $q->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
            }])
            ->paginate(15);

        $divisis    = User::where('role', 'karyawan')->whereNotNull('divisi')->distinct()->pluck('divisi');

        // Hitung hari kerja (Senin-Jumat) di bulan ini, tidak lebih dari hari ini
        $jumlahHariKerja = 0;
        $awalBulan = Carbon::create($tahun, $bulan, 1);
        $akhirBulan = $awalBulan->copy()->endOfMonth();
        $batas = $akhirBulan->lt(now()) ? $akhirBulan : now();
        $tgl = $awalBulan->copy();
        while ($tgl->lte($batas)) {
            if (!$tgl->isWeekend()) $jumlahHariKerja++;
            $tgl->addDay();
        }

        $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        return view('admin.rekap.index', compact(
            'karyawans', 'bulan', 'tahun', 'divisis', 'divisi', 'search',
            'jumlahHari', 'jumlahHariKerja'
        ));
    }

    public function detail(Request $request, User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 403);

        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);

        $absensis   = Absensi::where('user_id', $karyawan->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal')
            ->get();

        $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

        return view('admin.rekap.detail', compact('karyawan', 'absensis', 'bulan', 'tahun', 'jumlahHari'));
    }

    /**
     * Rekap tahunan – ringkasan per bulan sepanjang tahun
     */
    public function tahunan(Request $request)
    {
        $tahun  = $request->input('tahun', now()->year);
        $divisi = $request->input('divisi');
        $search = $request->input('search');

        $karyawans = User::where('role', 'karyawan')
            ->when($divisi, fn($q) => $q->where('divisi', $divisi))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%"))
            ->get();

        $divisis = User::where('role', 'karyawan')->whereNotNull('divisi')->distinct()->pluck('divisi');

        // Hitung statistik per karyawan per bulan
        $data = [];
        foreach ($karyawans as $karyawan) {
            $rekapBulan = [];
            $totalHadir = 0;
            $totalIzin  = 0;
            $totalAlpha = 0;
            $totalTelat = 0;

            for ($m = 1; $m <= 12; $m++) {
                $absensis = Absensi::where('user_id', $karyawan->id)
                    ->whereMonth('tanggal', $m)
                    ->whereYear('tanggal', $tahun)
                    ->get();

                $hadir  = $absensis->where('status', 'hadir')->count();
                $izin   = $absensis->whereIn('status', ['izin', 'sakit'])->count();
                $telat  = $absensis->where('keterangan', 'Terlambat')->count();

                // Hitung hari kerja bulan ini tidak lebih dari hari ini
                $awal   = Carbon::create($tahun, $m, 1);
                $akhir  = $awal->copy()->endOfMonth();
                $batas  = $akhir->lt(now()) ? $akhir : now();
                $hariKerja = 0;
                $tgl = $awal->copy();
                while ($tgl->lte($batas)) {
                    if (!$tgl->isWeekend()) $hariKerja++;
                    $tgl->addDay();
                }

                // Alpha = hari kerja - semua record absensi
                $alpha = max(0, $hariKerja - $absensis->count());

                $rekapBulan[$m] = compact('hadir', 'izin', 'alpha', 'telat');
                $totalHadir += $hadir;
                $totalIzin  += $izin;
                $totalAlpha += $alpha;
                $totalTelat += $telat;
            }

            $data[] = [
                'karyawan'    => $karyawan,
                'rekap_bulan' => $rekapBulan,
                'total_hadir' => $totalHadir,
                'total_izin'  => $totalIzin,
                'total_alpha' => $totalAlpha,
                'total_telat' => $totalTelat,
            ];
        }

        return view('admin.rekap.tahunan', compact('data', 'tahun', 'divisis', 'divisi', 'search'));
    }
}
