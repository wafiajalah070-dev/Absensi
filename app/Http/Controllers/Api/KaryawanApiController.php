<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KaryawanApiController extends Controller
{
    /**
     * GET /api/admin/karyawan - Daftar semua karyawan
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $divisi = $request->input('divisi');

        $karyawans = User::where('role', 'karyawan')
            ->when($search, fn($q) => $q->where('name', 'like', "%$search%")
                ->orWhere('nip', 'like', "%$search%"))
            ->when($divisi, fn($q) => $q->where('divisi', $divisi))
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $karyawans->items(),
            'meta'    => [
                'total'        => $karyawans->total(),
                'per_page'     => $karyawans->perPage(),
                'current_page' => $karyawans->currentPage(),
                'last_page'    => $karyawans->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/admin/karyawan/{id} - Detail karyawan
     */
    public function show(User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 404);

        $bulanIni = [
            'hadir' => Absensi::where('user_id', $karyawan->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->where('status', 'hadir')->count(),
            'izin'  => Absensi::where('user_id', $karyawan->id)
                ->whereMonth('tanggal', now()->month)->whereYear('tanggal', now()->year)
                ->whereIn('status', ['izin','sakit'])->count(),
        ];

        return response()->json([
            'success' => true,
            'data'    => array_merge($karyawan->toArray(), ['statistik_bulan_ini' => $bulanIni]),
        ]);
    }

    /**
     * GET /api/admin/rekap - Rekap absensi semua karyawan
     */
    public function rekap(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);

        // Hitung hari kerja
        $awal  = Carbon::create($tahun, $bulan, 1);
        $akhir = $awal->copy()->endOfMonth();
        $batas = $akhir->lt(now()) ? $akhir : now();
        $hk = 0;
        $t = $awal->copy();
        while ($t->lte($batas)) { if (!$t->isWeekend()) $hk++; $t->addDay(); }

        $karyawans = User::where('role', 'karyawan')->get()->map(function ($k) use ($bulan, $tahun, $hk) {
            $abs    = Absensi::where('user_id', $k->id)
                ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();
            $hadir  = $abs->where('status', 'hadir')->count();
            $izin   = $abs->whereIn('status', ['izin','sakit'])->count();
            $alpha  = max(0, $hk - $abs->count());
            $persen = $hk > 0 ? round($hadir / $hk * 100) : 0;

            return [
                'id'           => $k->id,
                'nip'          => $k->nip,
                'nama'         => $k->name,
                'divisi'       => $k->divisi,
                'hadir'        => $hadir,
                'izin'         => $izin,
                'alpha'        => $alpha,
                'hari_kerja'   => $hk,
                'persen_hadir' => $persen,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $karyawans,
            'meta'    => ['bulan' => $bulan, 'tahun' => $tahun, 'hari_kerja' => $hk],
        ]);
    }

    /**
     * GET /api/admin/dashboard - Statistik dashboard
     */
    public function dashboard()
    {
        $totalKaryawan = User::where('role', 'karyawan')->count();
        $hadirHariIni  = Absensi::whereDate('tanggal', today())->where('status', 'hadir')->count();
        $izinHariIni   = Absensi::whereDate('tanggal', today())->whereIn('status', ['izin','sakit'])->count();
        $alphaHariIni  = max(0, $totalKaryawan - Absensi::whereDate('tanggal', today())->count());

        return response()->json([
            'success' => true,
            'data'    => [
                'total_karyawan'  => $totalKaryawan,
                'hadir_hari_ini'  => $hadirHariIni,
                'izin_hari_ini'   => $izinHariIni,
                'alpha_hari_ini'  => $alphaHariIni,
                'tanggal'         => now()->translatedFormat('l, d F Y'),
                'jam'             => now()->format('H:i') . ' WIB',
            ],
        ]);
    }
}
