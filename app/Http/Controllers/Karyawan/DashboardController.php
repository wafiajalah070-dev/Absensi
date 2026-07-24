<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user        = Auth::user();
        $absensiHari = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        $riwayat = Absensi::where('user_id', $user->id)
            ->latest('tanggal')
            ->take(7)
            ->get();

        $bulanIni = [
            'hadir' => Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->where('status', 'hadir')->count(),
            'izin'  => Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->whereIn('status', ['izin','sakit'])->count(),
            'alpha' => Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->where('status', 'alpha')->count(),
            'telat' => Absensi::where('user_id', $user->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->where('keterangan', 'Terlambat')->count(),
        ];

        // Grafik kehadiran 30 hari terakhir (per minggu)
        $grafikMingguan = collect();
        for ($i = 3; $i >= 0; $i--) {
            $mulai = Carbon::today()->subWeeks($i)->startOfWeek();
            $akhir = Carbon::today()->subWeeks($i)->endOfWeek();
            $grafikMingguan->push([
                'label' => 'Minggu ' . ($mulai->format('d M')),
                'hadir' => Absensi::where('user_id', $user->id)
                    ->whereBetween('tanggal', [$mulai, $akhir])
                    ->where('status', 'hadir')->count(),
                'alpha' => Absensi::where('user_id', $user->id)
                    ->whereBetween('tanggal', [$mulai, $akhir])
                    ->where('status', 'alpha')->count(),
            ]);
        }

        // Izin terakhir
        $izinTerakhir = Izin::where('user_id', $user->id)->latest()->take(3)->get();

        // Persentase kehadiran bulan ini
        $hariKerja = 0;
        $awal = now()->startOfMonth();
        $hari = $awal->copy();
        while ($hari->lte(now())) {
            if (!$hari->isWeekend()) $hariKerja++;
            $hari->addDay();
        }
        $persenHadir = $hariKerja > 0 ? round($bulanIni['hadir'] / $hariKerja * 100) : 0;

        return view('karyawan.dashboard', compact(
            'user', 'absensiHari', 'riwayat', 'bulanIni',
            'grafikMingguan', 'izinTerakhir', 'persenHadir', 'hariKerja'
        ));
    }
}
