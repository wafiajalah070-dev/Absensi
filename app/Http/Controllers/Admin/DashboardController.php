<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalKaryawan = User::where('role', 'karyawan')->count();
        $hadirHariIni  = Absensi::whereDate('tanggal', today())->where('status', 'hadir')->count();
        $izinHariIni   = Absensi::whereDate('tanggal', today())->whereIn('status', ['izin','sakit'])->count();
        $alphaHariIni  = max(0, $totalKaryawan - Absensi::whereDate('tanggal', today())->count());
        $pendingIzin   = Izin::where('status', 'pending')->count();

        $absensiTerbaru = Absensi::with('user')
            ->whereDate('tanggal', today())
            ->latest()
            ->take(8)
            ->get();

        // Karyawan yang belum absen hari ini (alpha) – untuk tabel dashboard
        $sudahAbsenIds = Absensi::whereDate('tanggal', today())->pluck('user_id');
        $karyawanAlpha = User::where('role', 'karyawan')
            ->whereNotIn('id', $sudahAbsenIds)
            ->take(max(0, 8 - $absensiTerbaru->count()))
            ->get()
            ->map(function ($u) {
                return (object)[
                    'user'       => $u,
                    'jam_masuk'  => null,
                    'jam_keluar' => null,
                    'status'     => 'alpha',
                    'keterangan' => null,
                ];
            });

        // ── Grafik 7 hari terakhir ──────────────────────────
        $totalKaryawan7 = User::where('role', 'karyawan')->count();
        $grafik7Hari = collect();
        for ($i = 6; $i >= 0; $i--) {
            $tgl       = Carbon::today()->subDays($i);
            $isHariKerja = !$tgl->isWeekend();
            $absCount  = Absensi::whereDate('tanggal', $tgl)->count();
            $hadir     = Absensi::whereDate('tanggal', $tgl)->where('status', 'hadir')->count();
            $izin      = Absensi::whereDate('tanggal', $tgl)->whereIn('status', ['izin','sakit'])->count();
            // Alpha = karyawan yang tidak absen sama sekali (hanya hari kerja)
            $alpha     = $isHariKerja ? max(0, $totalKaryawan7 - $absCount) : 0;

            $grafik7Hari->push([
                'label'  => $tgl->translatedFormat('D, d M'),
                'hadir'  => $hadir,
                'izin'   => $izin,
                'alpha'  => $alpha,
            ]);
        }

        // ── Grafik pie bulan ini ────────────────────────────
        $hariKerjaBulanIni = 0;
        $awalBulan = Carbon::today()->startOfMonth();
        $tglCek = $awalBulan->copy();
        $batasBulan = now();
        while ($tglCek->lte($batasBulan)) {
            if (!$tglCek->isWeekend()) $hariKerjaBulanIni++;
            $tglCek->addDay();
        }
        $totalAbsenBulanIni = Absensi::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)->count();

        $bulanIni = [
            'hadir' => Absensi::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->where('status', 'hadir')->count(),
            'izin'  => Absensi::whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->whereIn('status', ['izin','sakit'])->count(),
            'alpha' => max(0, ($hariKerjaBulanIni * $totalKaryawan) - $totalAbsenBulanIni),
        ];

        // ── Top 5 karyawan paling sering alpha bulan ini ───
        // Hitung alpha per karyawan: hari kerja - total absensi tercatat
        $semueKaryawan = User::where('role', 'karyawan')->get();
        $topAlpha = $semueKaryawan->map(function ($k) use ($hariKerjaBulanIni) {
            $totalAbsen = Absensi::where('user_id', $k->id)
                ->whereMonth('tanggal', now()->month)
                ->whereYear('tanggal', now()->year)
                ->count();
            $k->jumlah_alpha = max(0, $hariKerjaBulanIni - $totalAbsen);
            return $k;
        })->sortByDesc('jumlah_alpha')->take(5);

        return view('admin.dashboard', compact(
            'totalKaryawan', 'hadirHariIni', 'izinHariIni', 'alphaHariIni',
            'pendingIzin', 'absensiTerbaru', 'karyawanAlpha', 'grafik7Hari', 'bulanIni', 'topAlpha'
        ));
    }
}
