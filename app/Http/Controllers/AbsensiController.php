<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiController extends Controller
{
    /**
     * Halaman scan QR – bisa diakses tanpa login (dari HP karyawan)
     */
    public function scan(string $token)
    {
        $karyawan = User::where('qr_token', $token)->where('role', 'karyawan')->firstOrFail();

        $absensiHari = Absensi::where('user_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('absensi.scan', compact('karyawan', 'absensiHari', 'token'));
    }

    /**
     * Proses absen masuk / keluar dari scan QR
     */
    public function proses(Request $request, string $token)
    {
        $karyawan = User::where('qr_token', $token)->where('role', 'karyawan')->firstOrFail();

        $absensiHari = Absensi::where('user_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        $aksi = $request->input('aksi');

        if ($aksi === 'masuk') {
            if ($absensiHari) {
                return back()->with('error', 'Anda sudah absen masuk hari ini.');
            }
            Absensi::create([
                'user_id'   => $karyawan->id,
                'tanggal'   => today(),
                'jam_masuk' => now()->format('H:i:s'),
                'status'    => 'hadir',
            ]);
            return redirect()->route('absensi.scan', $token)
                ->with('success', 'Absen masuk berhasil! Jam: ' . now()->format('H:i'));
        }

        if ($aksi === 'keluar') {
            if (!$absensiHari) {
                return back()->with('error', 'Anda belum absen masuk hari ini.');
            }
            if ($absensiHari->jam_keluar) {
                return back()->with('error', 'Anda sudah absen keluar hari ini.');
            }
            $absensiHari->update(['jam_keluar' => now()->format('H:i:s')]);
            return redirect()->route('absensi.scan', $token)
                ->with('success', 'Absen keluar berhasil! Jam: ' . now()->format('H:i'));
        }

        return back()->with('error', 'Aksi tidak valid.');
    }

    /**
     * Riwayat absensi karyawan
     */
    public function riwayat(Request $request)
    {
        $user   = Auth::user();
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);

        $absensis = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        return view('karyawan.riwayat', compact('absensis', 'bulan', 'tahun'));
    }

    /**
     * Halaman QR karyawan (ditunjukkan ke admin, bisa offline)
     */
    public function qrSaya()
    {
        $user = Auth::user();
        return view('karyawan.qr-saya', compact('user'));
    }
}
