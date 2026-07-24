<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\AbsensiApiController;
use App\Http\Controllers\Api\KaryawanApiController;
use App\Http\Controllers\Api\IzinApiController;

/*
|--------------------------------------------------------------------------
| AbsensiKP REST API v1
|--------------------------------------------------------------------------
| Base URL: /api
| Auth: Bearer Token (Laravel Sanctum)
*/

// ── Public routes (tanpa auth) ──────────────────────────────
Route::prefix('v1')->group(function () {

    Route::post('/login',  [AuthApiController::class, 'login']);

    // ── Protected routes ─────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::post('/logout', [AuthApiController::class, 'logout']);
        Route::get('/me',      [AuthApiController::class, 'me']);

        // ── Karyawan routes ──────────────────────────────────
        Route::middleware('karyawan')->prefix('karyawan')->group(function () {
            // Absensi GPS
            Route::get('/absensi',          [AbsensiApiController::class, 'index']);
            Route::get('/absensi/hari-ini', [AbsensiApiController::class, 'hariIni']);
            Route::post('/absensi/masuk',   [AbsensiApiController::class, 'masuk']);
            Route::post('/absensi/keluar',  [AbsensiApiController::class, 'keluar']);

            // Izin
            Route::get('/izin',         [IzinApiController::class, 'index']);
            Route::post('/izin',        [IzinApiController::class, 'store']);
            Route::delete('/izin/{izin}', [IzinApiController::class, 'destroy']);
        });

        // ── Admin routes ─────────────────────────────────────
        Route::middleware('admin')->prefix('admin')->group(function () {
            Route::get('/dashboard',          [KaryawanApiController::class, 'dashboard']);
            Route::get('/karyawan',           [KaryawanApiController::class, 'index']);
            Route::get('/karyawan/{karyawan}',[KaryawanApiController::class, 'show']);
            Route::get('/rekap',              [KaryawanApiController::class, 'rekap']);

            // Kelola izin
            Route::get('/izin',               [IzinApiController::class, 'adminIndex']);
            Route::put('/izin/{izin}',        [IzinApiController::class, 'updateStatus']);
        });
    });
});

// ── API Info ─────────────────────────────────────────────────
Route::get('/', fn() => response()->json([
    'app'     => 'AbsensiKP REST API',
    'version' => 'v1',
    'base_url'=> url('/api/v1'),
    'endpoints' => [
        'POST /api/v1/login'                     => 'Login & dapat token',
        'POST /api/v1/logout'                    => 'Logout [Auth]',
        'GET  /api/v1/me'                        => 'Profil user [Auth]',
        'GET  /api/v1/karyawan/absensi'          => 'Riwayat absensi [Karyawan]',
        'GET  /api/v1/karyawan/absensi/hari-ini' => 'Status absensi hari ini [Karyawan]',
        'POST /api/v1/karyawan/absensi/masuk'    => 'Absen masuk GPS [Karyawan]',
        'POST /api/v1/karyawan/absensi/keluar'   => 'Absen keluar GPS [Karyawan]',
        'GET  /api/v1/karyawan/izin'             => 'Daftar izin [Karyawan]',
        'POST /api/v1/karyawan/izin'             => 'Ajukan izin [Karyawan]',
        'GET  /api/v1/admin/dashboard'           => 'Statistik dashboard [Admin]',
        'GET  /api/v1/admin/karyawan'            => 'Daftar karyawan [Admin]',
        'GET  /api/v1/admin/rekap'               => 'Rekap absensi [Admin]',
        'GET  /api/v1/admin/izin'                => 'Semua pengajuan izin [Admin]',
        'PUT  /api/v1/admin/izin/{id}'           => 'Setujui/tolak izin [Admin]',
    ],
]));
