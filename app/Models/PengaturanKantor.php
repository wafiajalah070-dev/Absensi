<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanKantor extends Model
{
    protected $table = 'pengaturan_kantors';

    protected $fillable = [
        'nama_kantor',
        'latitude',
        'longitude',
        'radius_meter',
        'jam_masuk_mulai',
        'jam_masuk_batas',
        'jam_keluar_minimal',
        'wajib_lokasi',
    ];

    protected $casts = [
        'wajib_lokasi' => 'boolean',
        'latitude'     => 'float',
        'longitude'    => 'float',
    ];

    /**
     * Hitung jarak antara dua koordinat (Haversine formula) dalam meter
     */
    public function hitungJarak(float $lat, float $lng): float
    {
        if (!$this->latitude || !$this->longitude) return 0;

        $earthRadius = 6371000; // meter
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2)
           + cos(deg2rad($this->latitude)) * cos(deg2rad($lat))
           * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }

    /**
     * Cek apakah koordinat dalam radius
     */
    public function dalamRadius(float $lat, float $lng): bool
    {
        if (!$this->wajib_lokasi) return true;
        if (!$this->latitude || !$this->longitude) return true;
        return $this->hitungJarak($lat, $lng) <= $this->radius_meter;
    }
}
