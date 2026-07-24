<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $search    = $request->input('search');
        $karyawans = User::where('role', 'karyawan')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('nip', 'like', "%{$search}%")
                ->orWhere('divisi', 'like', "%{$search}%"))
            ->latest()
            ->paginate(10);

        return view('admin.karyawan.index', compact('karyawans', 'search'));
    }

    public function create()
    {
        return view('admin.karyawan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'nip'      => 'nullable|string|unique:users,nip',
            'jabatan'  => 'nullable|string|max:100',
            'divisi'   => 'nullable|string|max:100',
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'email.required'     => 'Email wajib diisi.',
            'email.unique'       => 'Email sudah digunakan.',
            'password.required'  => 'Password wajib diisi.',
            'password.min'       => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'nip.unique'         => 'NIP sudah digunakan.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role']     = 'karyawan';
        $validated['qr_token'] = Str::uuid();

        User::create($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function edit(User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 403);
        return view('admin.karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 403);

        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $karyawan->id,
            'nip'     => 'nullable|string|unique:users,nip,' . $karyawan->id,
            'jabatan' => 'nullable|string|max:100',
            'divisi'  => 'nullable|string|max:100',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => 'min:6|confirmed',
            ]);
            $validated['password'] = Hash::make($request->password);
        }

        $karyawan->update($validated);

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 403);
        $karyawan->delete();

        return redirect()->route('admin.karyawan.index')
            ->with('success', 'Karyawan berhasil dihapus.');
    }

    public function qrcode(User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 403);
        return view('admin.karyawan.qrcode', compact('karyawan'));
    }

    public function regenerateQr(User $karyawan)
    {
        abort_if($karyawan->role !== 'karyawan', 403);
        $karyawan->update(['qr_token' => \Illuminate\Support\Str::uuid()]);

        return redirect()->route('admin.karyawan.qrcode', $karyawan)
            ->with('success', 'QR Code berhasil diperbarui.');
    }
}
