<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use Illuminate\Http\Request;

class IzinApiController extends Controller
{
    /**
     * GET /api/izin - Daftar izin karyawan yang login
     */
    public function index(Request $request)
    {
        $izins = Izin::where('user_id', $request->user()->id)
            ->latest()->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $izins->items(),
            'meta'    => ['total' => $izins->total(), 'current_page' => $izins->currentPage()],
        ]);
    }

    /**
     * POST /api/izin - Ajukan izin baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis'           => 'required|in:izin,sakit,cuti',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'alasan'          => 'required|string|min:10|max:500',
        ]);

        // Cek bentrok
        $bentrok = Izin::where('user_id', $request->user()->id)
            ->where('status', '!=', 'ditolak')
            ->where(fn($q) => $q
                ->whereBetween('tanggal_mulai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
                ->orWhereBetween('tanggal_selesai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
            )->exists();

        if ($bentrok) {
            return response()->json([
                'success' => false,
                'message' => 'Sudah ada pengajuan izin pada tanggal tersebut.',
            ], 422);
        }

        $validated['user_id'] = $request->user()->id;
        $izin = Izin::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan izin berhasil dikirim.',
            'data'    => $izin,
        ], 201);
    }

    /**
     * DELETE /api/izin/{id} - Batalkan izin (hanya pending)
     */
    public function destroy(Request $request, Izin $izin)
    {
        if ($izin->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }
        if ($izin->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Izin yang sudah diproses tidak bisa dibatalkan.'], 422);
        }
        $izin->delete();
        return response()->json(['success' => true, 'message' => 'Pengajuan izin dibatalkan.']);
    }

    /**
     * GET /api/admin/izin - Semua pengajuan izin (admin only)
     */
    public function adminIndex(Request $request)
    {
        $status = $request->input('status', 'pending');
        $izins  = Izin::with('user')
            ->when($status !== 'semua', fn($q) => $q->where('status', $status))
            ->latest()->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $izins->items(),
            'meta'    => ['total' => $izins->total(), 'status_filter' => $status],
        ]);
    }

    /**
     * PUT /api/admin/izin/{id} - Setujui atau tolak izin (admin)
     */
    public function updateStatus(Request $request, Izin $izin)
    {
        $request->validate([
            'status'        => 'required|in:disetujui,ditolak',
            'catatan_admin' => 'nullable|string|max:255',
        ]);

        if ($request->status === 'ditolak' && !$request->catatan_admin) {
            return response()->json(['success' => false, 'message' => 'Alasan penolakan wajib diisi.'], 422);
        }

        $izin->update([
            'status'        => $request->status,
            'catatan_admin' => $request->catatan_admin,
        ]);

        if ($request->status === 'disetujui') {
            $tanggal = $izin->tanggal_mulai->copy();
            while ($tanggal->lte($izin->tanggal_selesai)) {
                if (!$tanggal->isWeekend()) {
                    \App\Models\Absensi::updateOrCreate(
                        ['user_id' => $izin->user_id, 'tanggal' => $tanggal->format('Y-m-d')],
                        ['status' => $izin->jenis, 'keterangan' => $izin->alasan]
                    );
                }
                $tanggal->addDay();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status izin berhasil diperbarui.',
            'data'    => $izin->fresh(),
        ]);
    }
}
