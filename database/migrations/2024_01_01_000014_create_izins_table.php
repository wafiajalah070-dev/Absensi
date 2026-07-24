<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('izins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('jenis', ['izin', 'sakit', 'cuti'])->default('izin');
            $table->text('alasan');
            $table->string('lampiran')->nullable()->comment('File pendukung');
            $table->enum('status', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->string('catatan_admin')->nullable();
            $table->timestamps();
        });

        // Update default jam kerja di pengaturan_kantors
        Schema::table('pengaturan_kantors', function (Blueprint $table) {
            $table->time('jam_masuk_mulai')->default('07:00:00')->change();
            $table->time('jam_masuk_batas')->default('10:00:00')->change();
            $table->time('jam_keluar_minimal')->default('17:00:00')->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('izins');
    }
};
