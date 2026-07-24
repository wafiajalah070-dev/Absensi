<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_kantors', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kantor')->default('Kantor Pusat');
            $table->decimal('latitude',  10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('radius_meter')->default(100)->comment('Radius absensi yang diizinkan');
            $table->time('jam_masuk_mulai')->default('07:00:00');
            $table->time('jam_masuk_batas')->default('09:00:00')->comment('Batas tidak terlambat');
            $table->time('jam_keluar_minimal')->default('16:00:00');
            $table->boolean('wajib_lokasi')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_kantors');
    }
};
