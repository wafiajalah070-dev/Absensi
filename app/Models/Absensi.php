<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensis';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'status',
        'keterangan',
        'lokasi_masuk',
        'lokasi_keluar',
        'lat_masuk',
        'lng_masuk',
        'lat_keluar',
        'lng_keluar',
        'jarak_masuk',
        'jarak_keluar',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'lat_masuk'    => 'float',
        'lng_masuk'    => 'float',
        'lat_keluar'   => 'float',
        'lng_keluar'   => 'float',
        'jarak_masuk'  => 'float',
        'jarak_keluar' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getTerlambatAttribute(): bool
    {
        if (!$this->jam_masuk) return false;
        return $this->jam_masuk > '08:00:00';
    }
}
