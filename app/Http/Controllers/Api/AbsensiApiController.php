<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\PengaturanKantor;
use Illuminate\Http\Request;

class AbsensiApiController extends Controller
{
    /**
     * GET /api/absensi - Riwayat absensi karyawan yang login
     */
    public function index(Request $request)
    {
        $bulan  = $request->input('bulan', now()->month);
        $tahun  = $request->input('tahun', now()->year);

        $absensis = Absensi::where('user_id', $request->user()->id)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get()
            ->map(fn($a) => $this->formatAbsensi($a));

        return response()->json([
            'success' => true,
            'data'    => $absensis,
            'meta'    => ['bulan' => $bulan, 'tahun' => $tahun, 'total' => $absensis->count()],
        ]);
    }

    /**
     * GET /api/absensi/hari-ini - Status absensi hari ini
     */
    public function hariIni(Request $request)
    {
        $absensi = Absensi::where('user_id', $request->user()->id)
            ->whereDate('tanggal', today())
            ->first();

        return response()->json([
            'success'    => true,
            'data'       => $absensi ? $this->formatAbsensi($absensi) : null,
            'sudah_masuk'  => $absensi !== null,
            'sudah_keluar' => $absensi?->jam_keluar !== null,
        ]);
    }

    /**
     * POST /api/absensi/masuk - Absen masuk via GPS
     */
    public function masuk(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat'    => 'nullable|string|max:255',
        ]);

        $user   = $request->user();
        $kantor = PengaturanKantor::first();

        // Cek sudah absen masuk
        $absensiHari = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())->first();

        if ($absensiHari) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah absen masuk hari ini pukul ' .
                    \Carbon\Carbon::parse($absensiHari->jam_masuk)->format('H:i') . '.',
            ], 422);
        }

        $lat = (float) $request->latitude;
        $lng = (float) $request->longitude;
        $jarak = 0;

        // Validasi lokasi
        if ($kantor && $kantor->wajib_lokasi && $kantor->latitude && $kantor->longitude) {
            $jarak = $kantor->hitungJarak($lat, $lng);
            if (!$kantor->dalamRadius($lat, $lng)) {
                return response()->json([
                    'success' => false,
                    'message' => "Anda berada {$jarak}m dari kantor. Maksimal {$kantor->radius_meter}m.",
                    'jarak'   => $jarak,
                ], 422);
            }
        }

        $jamSekarang = now()->format('H:i:s');
        $batasTelat  = $kantor?->jam_masuk_batas ?? '10:00:00';
        $keterangan  = $jamSekarang > $batasTelat ? 'Terlambat' : null;

        $absensi = Absensi::create([
            'user_id'      => $user->id,
            'tanggal'      => today(),
            'jam_masuk'    => $jamSekarang,
            'status'       => 'hadir',
            'keterangan'   => $keterangan,
            'lokasi_masuk' => $request->alamat ?? "$lat,$lng",
            'lat_masuk'    => $lat,
            'lng_masuk'    => $lng,
            'jarak_masuk'  => $jarak,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen masuk berhasil!' . ($keterangan ? ' (Terlambat)' : ''),
            'data'    => $this->formatAbsensi($absensi),
        ], 201);
    }

    /**
     * POST /api/absensi/keluar - Absen keluar via GPS
     */
    public function keluar(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat'    => 'nullable|string|max:255',
        ]);

        $user        = $request->user();
        $absensiHari = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', today())->first();

        if (!$absensiHari) {
            return response()->json(['success' => false, 'message' => 'Anda belum absen masuk hari ini.'], 422);
        }
        if ($absensiHari->jam_keluar) {
            return response()->json(['success' => false, 'message' => 'Anda sudah absen keluar hari ini.'], 422);
        }

        $lat   = (float) $request->latitude;
        $lng   = (float) $request->longitude;
        $jarak = 0;
        $kantor = PengaturanKantor::first();
        if ($kantor && $kantor->wajib_lokasi && $kantor->latitude) {
            $jarak = $kantor->hitungJarak($lat, $lng);
        }

        $absensiHari->update([
            'jam_keluar'    => now()->format('H:i:s'),
            'lokasi_keluar' => $request->alamat ?? "$lat,$lng",
            'lat_keluar'    => $lat,
            'lng_keluar'    => $lng,
            'jarak_keluar'  => $jarak,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen keluar berhasil!',
            'data'    => $this->formatAbsensi($absensiHari->fresh()),
        ]);
    }

    private function formatAbsensi(Absensi $a): array
    {
        return [
            'id'           => $a->id,
            'tanggal'      => $a->tanggal->format('Y-m-d'),
            'hari'         => $a->tanggal->translatedFormat('l'),
            'jam_masuk'    => $a->jam_masuk ? \Carbon\Carbon::parse($a->jam_masuk)->format('H:i') : null,
            'jam_keluar'   => $a->jam_keluar ? \Carbon\Carbon::parse($a->jam_keluar)->format('H:i') : null,
            'status'       => $a->status,
            'keterangan'   => $a->keterangan,
            'lokasi_masuk' => $a->lokasi_masuk,
            'jarak_masuk'  => $a->jarak_masuk,
        ];
    }
}
