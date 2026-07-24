<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Izin extends Model
{
    use HasFactory;

    protected $table = 'izins';

    protected $fillable = [
        'user_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis',
        'alasan',
        'lampiran',
        'status',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getJumlahHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai) + 1;
    }

    public function getBadgeStatusAttribute(): string
    {
        return match($this->status) {
            'disetujui' => '<span class="badge bg-success">Disetujui</span>',
            'ditolak'   => '<span class="badge bg-danger">Ditolak</span>',
            default     => '<span class="badge bg-warning text-dark">Menunggu</span>',
        };
    }

    public function getBadgeJenisAttribute(): string
    {
        return match($this->jenis) {
            'sakit' => '<span class="badge bg-info">Sakit</span>',
            'cuti'  => '<span class="badge bg-primary">Cuti</span>',
            default => '<span class="badge bg-secondary">Izin</span>',
        };
    }
}
