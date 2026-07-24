<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Izin;
use Illuminate\Http\Request;

class IzinController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $izins  = Izin::with('user')
            ->when($status !== 'semua', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15);

        $jumlahPending = Izin::where('status', 'pending')->count();

        return view('admin.izin.index', compact('izins', 'status', 'jumlahPending'));
    }

    public function setujui(Request $request, Izin $izin)
    {
        $izin->update([
            'status'        => 'disetujui',
            'catatan_admin' => $request->input('catatan_admin'),
        ]);

        // Buat/update record absensi untuk setiap hari izin
        $tanggal = $izin->tanggal_mulai->copy();
        while ($tanggal->lte($izin->tanggal_selesai)) {
            if (!$tanggal->isWeekend()) {
                Absensi::updateOrCreate(
                    ['user_id' => $izin->user_id, 'tanggal' => $tanggal->format('Y-m-d')],
                    ['status' => $izin->jenis, 'keterangan' => $izin->alasan]
                );
            }
            $tanggal->addDay();
        }

        return back()->with('success', 'Izin ' . $izin->user->name . ' telah disetujui.');
    }

    public function tolak(Request $request, Izin $izin)
    {
        $request->validate(['catatan_admin' => 'required|string|max:255'], [
            'catatan_admin.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $izin->update([
            'status'        => 'ditolak',
            'catatan_admin' => $request->catatan_admin,
        ]);

        return back()->with('success', 'Izin ' . $izin->user->name . ' telah ditolak.');
    }
}
