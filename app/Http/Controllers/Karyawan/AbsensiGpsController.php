<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AbsensiGpsController extends Controller
{
    /**
     * Halaman absensi GPS karyawan
     */
    public function index()
    {
        $user        = Auth::user();
        $kantor      = PengaturanKantor::first();
        $absensiHari = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        return view('karyawan.absensi-gps', compact('user', 'kantor', 'absensiHari'));
    }

    /**
     * Proses absensi GPS (AJAX)
     */
    public function proses(Request $request)
    {
        $request->validate([
            'aksi'      => 'required|in:masuk,keluar',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat'    => 'nullable|string|max:255',
        ]);

        $user        = Auth::user();
        $kantor      = PengaturanKantor::first();
        $absensiHari = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())
            ->first();

        $lat    = (float) $request->latitude;
        $lng    = (float) $request->longitude;
        $alamat = $request->alamat ?? 'Lokasi tidak diketahui';
        $jarak  = 0;

        // Validasi lokasi jika kantor sudah dikonfigurasi
        if ($kantor && $kantor->wajib_lokasi && $kantor->latitude && $kantor->longitude) {
            $jarak = $kantor->hitungJarak($lat, $lng);

            if (!$kantor->dalamRadius($lat, $lng)) {
                return response()->json([
                    'sukses'  => false,
                    'pesan'   => "Anda berada di luar area kantor. Jarak Anda: " . number_format($jarak) . " meter dari kantor (maks: {$kantor->radius_meter} meter).",
                    'jarak'   => $jarak,
                    'di_area' => false,
                ]);
            }
        }

        if ($request->aksi === 'masuk') {
            if ($absensiHari) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Anda sudah absen masuk hari ini pukul ' .
                        \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i') . '.',
                ]);
            }

            // Cek keterlambatan
            $jamSekarang = now()->format('H:i:s');
            $batasLambat = $kantor?->jam_masuk_batas ?? '10:00:00';
            $jamMulai    = $kantor?->jam_masuk_mulai ?? '09:00:00';
            $keterangan  = null;

            if ($jamSekarang < $jamMulai) {
                return response()->json([
                    'sukses' => false,
                    'pesan'  => 'Absen masuk belum dibuka. Jam kerja mulai pukul ' .
                        substr($jamMulai, 0, 5) . ' WIB.',
                ]);
            }

            if ($jamSekarang > $batasLambat) {
                $keterangan = 'Terlambat';
            }

            Absensi::create([
                'user_id'      => $user->id,
                'tanggal'      => today(),
                'jam_masuk'    => $jamSekarang,
                'status'       => 'hadir',
                'keterangan'   => $keterangan,
                'lokasi_masuk' => $alamat,
                'lat_masuk'    => $lat,
                'lng_masuk'    => $lng,
                'jarak_masuk'  => $jarak,
            ]);

            $pesanTambahan = $keterangan ? ' ⚠️ Terlambat' : '';
            return response()->json([
                'sukses'  => true,
                'pesan'   => 'Absen masuk berhasil!' . $pesanTambahan,
                'jam'     => now()->format('H:i'),
                'jarak'   => $jarak,
                'di_area' => true,
            ]);
        }

        // Keluar
        if (!$absensiHari) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Anda belum absen masuk hari ini.',
            ]);
        }

        if ($absensiHari->jam_keluar) {
            return response()->json([
                'sukses' => false,
                'pesan'  => 'Anda sudah absen keluar hari ini pukul ' .
                    \Carbon\Carbon::parse($absensiHari->jam_keluar)->format('H:i') . '.',
            ]);
        }

        $absensiHari->update([
            'jam_keluar'    => now()->format('H:i:s'),
            'lokasi_keluar' => $alamat,
            'lat_keluar'    => $lat,
            'lng_keluar'    => $lng,
            'jarak_keluar'  => $jarak,
        ]);

        return response()->json([
            'sukses'  => true,
            'pesan'   => 'Absen keluar berhasil!',
            'jam'     => now()->format('H:i'),
            'jarak'   => $jarak,
            'di_area' => true,
        ]);
    }
}
