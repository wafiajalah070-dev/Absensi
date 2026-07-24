<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust semua proxy (ngrok)
        $middleware->trustProxies(at: '*');

        // Wajib verifikasi email untuk route tertentu
        $middleware->redirectUsersTo(function ($request) {
            return $request->user()?->isAdmin()
                ? route('admin.dashboard')
                : route('karyawan.dashboard');
        });

        $middleware->alias([
            'admin'    => \App\Http\Middleware\AdminMiddleware::class,
            'karyawan' => \App\Http\Middleware\KaryawanMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
