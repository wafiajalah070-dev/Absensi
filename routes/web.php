<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\KaryawanController;
use App\Http\Controllers\Admin\RekapController;
use App\Http\Controllers\Karyawan\DashboardController as KaryawanDashboard;

// ── Halaman utama
Route::get('/', fn() => redirect()->route('login'));

// ── Scan QR (akses publik)
Route::get('/scan/{token}', [AbsensiController::class, 'scan'])->name('absensi.scan');
Route::post('/scan/{token}', [AbsensiController::class, 'proses'])->name('absensi.proses');

// ── Admin (wajib login + verified + role admin)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Kelola karyawan
    Route::resource('karyawan', KaryawanController::class)->except(['show']);
    Route::get('/karyawan/{karyawan}/qrcode', [KaryawanController::class, 'qrcode'])->name('karyawan.qrcode');
    Route::post('/karyawan/{karyawan}/regenerate-qr', [KaryawanController::class, 'regenerateQr'])->name('karyawan.regenerate-qr');

    // Rekap absensi
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/tahunan', [RekapController::class, 'tahunan'])->name('rekap.tahunan');
    Route::get('/rekap/{karyawan}', [RekapController::class, 'detail'])->name('rekap.detail');

    // Export laporan
    Route::get('/export/excel', [App\Http\Controllers\Admin\ExportController::class, 'excel'])->name('export.excel');
    Route::get('/export/pdf', [App\Http\Controllers\Admin\ExportController::class, 'pdf'])->name('export.pdf');
    Route::get('/export/excel-tahunan', [App\Http\Controllers\Admin\ExportController::class, 'excelTahunan'])->name('export.excel-tahunan');
    Route::get('/export/pdf-tahunan', [App\Http\Controllers\Admin\ExportController::class, 'pdfTahunan'])->name('export.pdf-tahunan');

    // Scanner
    Route::get('/scanner', [App\Http\Controllers\Admin\ScannerController::class, 'index'])->name('scanner');
    Route::post('/scanner/proses', [App\Http\Controllers\Admin\ScannerController::class, 'proses'])->name('scanner.proses');
    Route::get('/scanner/status/{token}', [App\Http\Controllers\Admin\ScannerController::class, 'status'])->name('scanner.status');

    // Pengaturan Kantor
    Route::get('/pengaturan-kantor', [App\Http\Controllers\Admin\PengaturanKantorController::class, 'index'])->name('pengaturan-kantor');
    Route::post('/pengaturan-kantor', [App\Http\Controllers\Admin\PengaturanKantorController::class, 'simpan'])->name('pengaturan-kantor.simpan');

    // Kelola izin
    Route::get('/izin', [App\Http\Controllers\Admin\IzinController::class, 'index'])->name('izin.index');
    Route::post('/izin/{izin}/setujui', [App\Http\Controllers\Admin\IzinController::class, 'setujui'])->name('izin.setujui');
    Route::post('/izin/{izin}/tolak', [App\Http\Controllers\Admin\IzinController::class, 'tolak'])->name('izin.tolak');
});

// ── Karyawan (wajib login + verified + role karyawan)
Route::middleware(['auth', 'verified', 'karyawan'])->prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/dashboard', [KaryawanDashboard::class, 'index'])->name('dashboard');
    Route::get('/riwayat', [AbsensiController::class, 'riwayat'])->name('riwayat');
    Route::get('/qr-saya', [AbsensiController::class, 'qrSaya'])->name('qr-saya');

    // Absensi GPS
    Route::get('/absensi', [App\Http\Controllers\Karyawan\AbsensiGpsController::class, 'index'])->name('absensi');
    Route::post('/absensi/proses', [App\Http\Controllers\Karyawan\AbsensiGpsController::class, 'proses'])->name('absensi.proses');

    // Izin
    Route::get('/izin', [App\Http\Controllers\Karyawan\IzinController::class, 'index'])->name('izin.index');
    Route::get('/izin/ajukan', [App\Http\Controllers\Karyawan\IzinController::class, 'create'])->name('izin.create');
    Route::post('/izin', [App\Http\Controllers\Karyawan\IzinController::class, 'store'])->name('izin.store');
    Route::delete('/izin/{izin}', [App\Http\Controllers\Karyawan\IzinController::class, 'destroy'])->name('izin.destroy');
});

require __DIR__.'/auth.php';
