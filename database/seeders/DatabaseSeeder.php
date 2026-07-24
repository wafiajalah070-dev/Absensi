<?php

namespace Database\Seeders;

use App\Models\PengaturanKantor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::updateOrCreate(
            ['email' => 'wafiajalah070@gmail.com'],
            [
                'name'     => 'Administrator',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
                'nip'      => 'ADM001',
                'jabatan'  => 'Administrator',
                'divisi'   => 'Management',
            ]
        );

        // Contoh karyawan
        $karyawans = [
            ['name' => 'Budi Santoso', 'email' => 'budi@absensi.com', 'nip' => 'KRY001', 'jabatan' => 'Staff IT',  'divisi' => 'Teknologi'],
            ['name' => 'Siti Rahayu',  'email' => 'siti@absensi.com', 'nip' => 'KRY002', 'jabatan' => 'HRD',       'divisi' => 'SDM'],
            ['name' => 'Andi Pratama', 'email' => 'andi@absensi.com', 'nip' => 'KRY003', 'jabatan' => 'Akuntan',   'divisi' => 'Keuangan'],
            ['name' => 'Wafi Ajalah', 'email' => 'wafi@absensi.com', 'nip' => 'KRY004', 'jabatan' => 'Akuntan',   'divisi' => 'Keuangan'],
        ];

        foreach ($karyawans as $k) {
            User::updateOrCreate(
                ['email' => $k['email']],
                array_merge($k, [
                    'password' => Hash::make('karyawan123'),
                    'role'     => 'karyawan',
                    'qr_token' => Str::uuid(),
                ])
            );
        }

        // Pengaturan kantor default
        PengaturanKantor::updateOrCreate(
            ['id' => 1],
            [
                'nama_kantor'        => 'Kantor Pusat',
                'radius_meter'       => 100,
                'jam_masuk_mulai'    => '09:00:00',
                'jam_masuk_batas'    => '10:00:00',
                'jam_keluar_minimal' => '17:00:00',
                'wajib_lokasi'       => false,
            ]
        );
    }
}
