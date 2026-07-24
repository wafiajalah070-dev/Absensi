<?php

namespace App\Http\Controllers\Karyawan;

use App\Http\Controllers\Controller;
use App\Models\Izin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
{
    public function index()
    {
        $izins = Izin::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('karyawan.izin.index', compact('izins'));
    }

    public function create()
    {
        return view('karyawan.izin.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis'          => 'required|in:izin,sakit,cuti',
            'tanggal_mulai'  => 'required|date|after_or_equal:today',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
            'alasan'         => 'required|string|min:10|max:500',
            'lampiran'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'jenis.required'          => 'Jenis izin wajib dipilih.',
            'tanggal_mulai.required'  => 'Tanggal mulai wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh sebelum hari ini.',
            'tanggal_selesai.required'=> 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'alasan.required'         => 'Alasan wajib diisi.',
            'alasan.min'              => 'Alasan minimal 10 karakter.',
            'lampiran.mimes'          => 'File harus berupa JPG, PNG, atau PDF.',
            'lampiran.max'            => 'Ukuran file maksimal 2MB.',
        ]);

        // Cek apakah sudah ada izin di tanggal yang sama
        $bentrok = Izin::where('user_id', Auth::id())
            ->where('status', '!=', 'ditolak')
            ->where(function ($q) use ($validated) {
                $q->whereBetween('tanggal_mulai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']])
                  ->orWhereBetween('tanggal_selesai', [$validated['tanggal_mulai'], $validated['tanggal_selesai']]);
            })->exists();

        if ($bentrok) {
            return back()->withErrors(['tanggal_mulai' => 'Sudah ada pengajuan izin pada tanggal tersebut.'])->withInput();
        }

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('izin-lampiran', 'public');
        }

        $validated['user_id'] = Auth::id();
        Izin::create($validated);

        return redirect()->route('karyawan.izin.index')
            ->with('success', 'Pengajuan izin berhasil dikirim. Menunggu persetujuan admin.');
    }

    public function destroy(Izin $izin)
    {
        abort_if($izin->user_id !== Auth::id(), 403);
        abort_if($izin->status !== 'pending', 403, 'Izin yang sudah diproses tidak bisa dihapus.');

        if ($izin->lampiran) {
            Storage::disk('public')->delete($izin->lampiran);
        }
        $izin->delete();

        return back()->with('success', 'Pengajuan izin berhasil dibatalkan.');
    }
}
