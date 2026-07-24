<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Sheet;

class AbsensiExport implements WithMultipleSheets
{
    protected int $bulan;
    protected int $tahun;
    protected ?int $userId;

    public function __construct(int $bulan, int $tahun, ?int $userId = null)
    {
        $this->bulan  = $bulan;
        $this->tahun  = $tahun;
        $this->userId = $userId;
    }

    public function sheets(): array
    {
        return [
            new AbsensiRingkasanSheet($this->bulan, $this->tahun, $this->userId),
            new AbsensiDetailSheet($this->bulan, $this->tahun, $this->userId),
        ];
    }
}
