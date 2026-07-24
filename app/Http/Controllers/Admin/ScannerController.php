<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    /**
     * Halaman scanner kamera admin
     */
    public function index()
    {
        return view('admin.scanner');
    }

    /**
     * Cek status karyawan berdasarkan token (dipanggil via AJAX setelah scan)
     */
    public function status(string $token)
    {
        $karyawan = User::where('qr_token', $token)->where('role', 'karyawan')->first();

        if (!$karyawan) {
            return response()->json([
                'valid' => false,
                'pesan' => 'QR Code tidak valid atau bukan karyawan.',
            ]);
        }

        $absensiHari = Absensi::where('user_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        $aksiBerikutnya = 'masuk';
        $infoStatus     = 'Belum absen hari ini';

        if ($absensiHari) {
            if ($absensiHari->jam_keluar) {
                $aksiBerikutnya = 'selesai';
                $infoStatus     = 'Sudah absen masuk & keluar';
            } else {
                $aksiBerikutnya = 'keluar';
                $infoStatus     = 'Sudah absen masuk pukul ' .
                    \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i');
            }
        }

        return response()->json([
            'valid'           => true,
            'nama'            => $karyawan->name,
            'nip'             => $karyawan->nip ?? '-',
            'jabatan'         => $karyawan->jabatan ?? '-',
            'divisi'          => $karyawan->divisi ?? '-',
            'aksi_berikutnya' => $aksiBerikutnya,
            'info_status'     => $infoStatus,
            'jam_masuk'       => $absensiHari?->jam_masuk
                ? \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i')
                : null,
            'jam_keluar'      => $absensiHari?->jam_keluar
                ? \Carbon\Carbon::parse($absensiHari->jam_keluar)->format('H:i')
                : null,
        ]);
    }

    /**
     * Proses absen dari scanner admin (dipanggil via AJAX)
     */
    public function proses(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'aksi'  => 'required|in:masuk,keluar',
        ]);

        $karyawan = User::where('qr_token', $request->token)
            ->where('role', 'karyawan')
            ->first();

        if (!$karyawan) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'QR Code tidak valid.',
            ]);
        }

        $absensiHari = Absensi::where('user_id', $karyawan->id)
            ->whereDate('tanggal', today())
            ->first();

        if ($request->aksi === 'masuk') {
            if ($absensiHari) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => $karyawan->name . ' sudah absen masuk hari ini.',
                ]);
            }

            Absensi::create([
                'user_id'   => $karyawan->id,
                'tanggal'   => today(),
                'jam_masuk' => now()->format('H:i:s'),
                'status'    => 'hadir',
            ]);

            return response()->json([
                'sukses' => true,
                'pesan'  => 'Absen masuk ' . $karyawan->name . ' berhasil! Pukul ' . now()->format('H:i'),
                'jam'    => now()->format('H:i'),
            ]);
        }

        if ($request->aksi === 'keluar') {
            if (!$absensiHari) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => $karyawan->name . ' belum absen masuk hari ini.',
                ]);
            }

            if ($absensiHari->jam_keluar) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => $karyawan->name . ' sudah absen keluar hari ini.',
                ]);
            }

            $absensiHari->update(['jam_keluar' => now()->format('H:i:s')]);

            return response()->json([
                'sukses' => true,
                'pesan'  => 'Absen keluar ' . $karyawan->name . ' berhasil! Pukul ' . now()->format('H:i'),
                'jam'    => now()->format('H:i'),
            ]);
        }

        return response()->json(['sukses' => false, 'pesan' => 'Aksi tidak valid.']);
    }
}
