<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->decimal('lat_masuk',  10, 7)->nullable()->after('lokasi_masuk');
            $table->decimal('lng_masuk',  10, 7)->nullable()->after('lat_masuk');
            $table->decimal('lat_keluar', 10, 7)->nullable()->after('lokasi_keluar');
            $table->decimal('lng_keluar', 10, 7)->nullable()->after('lat_keluar');
            $table->decimal('jarak_masuk',  8, 2)->nullable()->after('lng_keluar')->comment('meter');
            $table->decimal('jarak_keluar', 8, 2)->nullable()->after('jarak_masuk')->comment('meter');
        });
    }

    public function down(): void
    {
        Schema::table('absensis', function (Blueprint $table) {
            $table->dropColumn([
                'lat_masuk','lng_masuk','lat_keluar','lng_keluar',
                'jarak_masuk','jarak_keluar',
            ]);
        });
    }
};
