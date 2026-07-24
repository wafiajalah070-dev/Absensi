<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'karyawan'])->default('karyawan')->after('email');
            $table->string('nip')->nullable()->unique()->after('role');
            $table->string('jabatan')->nullable()->after('nip');
            $table->string('divisi')->nullable()->after('jabatan');
            $table->string('qr_token')->nullable()->unique()->after('divisi');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nip', 'jabatan', 'divisi', 'qr_token']);
        });
    }
};
