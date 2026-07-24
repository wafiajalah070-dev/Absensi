<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengaturanKantor;
use Illuminate\Http\Request;

class PengaturanKantorController extends Controller
{
    public function index()
    {
        $kantor = PengaturanKantor::first() ?? new PengaturanKantor();
        return view('admin.pengaturan-kantor', compact('kantor'));
    }

    public function simpan(Request $request)
    {
        $validated = $request->validate([
            'nama_kantor'        => 'required|string|max:100',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'radius_meter'       => 'required|integer|min:10|max:5000',
            'jam_masuk_mulai'    => 'required',
            'jam_masuk_batas'    => 'required',
            'jam_keluar_minimal' => 'required',
            'wajib_lokasi'       => 'boolean',
        ]);

        $validated['wajib_lokasi'] = $request->boolean('wajib_lokasi');

        PengaturanKantor::updateOrCreate(['id' => 1], $validated);

        return redirect()->route('admin.pengaturan-kantor')
            ->with('success', 'Pengaturan kantor berhasil disimpan.');
    }
}
