<?php

namespace App\Exports;

use App\Models\Absensi;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AbsensiDetailSheet implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected int $bulan;
    protected int $tahun;
    protected ?int $userId;
    private int $no = 0;

    public function __construct(int $bulan, int $tahun, ?int $userId = null)
    {
        $this->bulan  = $bulan;
        $this->tahun  = $tahun;
        $this->userId = $userId;
    }

    public function collection()
    {
        return Absensi::with('user')
            ->whereMonth('tanggal', $this->bulan)
            ->whereYear('tanggal', $this->tahun)
            ->when($this->userId, fn($q) => $q->where('user_id', $this->userId))
            ->orderBy('user_id')
            ->orderBy('tanggal')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No', 'NIP', 'Nama Karyawan', 'Divisi', 'Jabatan',
            'Tanggal', 'Hari', 'Jam Masuk', 'Jam Keluar',
            'Durasi (jam)', 'Status', 'Keterangan',
        ];
    }

    public function map($row): array
    {
        $this->no++;
        $durasi = '-';
        if ($row->jam_masuk && $row->jam_keluar) {
            $menit  = Carbon::parse($row->jam_masuk)->diffInMinutes(Carbon::parse($row->jam_keluar));
            $durasi = round($menit / 60, 1) . ' jam';
        }

        return [
            $this->no,
            $row->user->nip    ?? '-',
            $row->user->name,
            $row->user->divisi  ?? '-',
            $row->user->jabatan ?? '-',
            Carbon::parse($row->tanggal)->format('d/m/Y'),
            Carbon::parse($row->tanggal)->translatedFormat('l'),
            $row->jam_masuk  ? Carbon::parse($row->jam_masuk)->format('H:i')  : '-',
            $row->jam_keluar ? Carbon::parse($row->jam_keluar)->format('H:i') : '-',
            $durasi,
            ucfirst($row->status),
            $row->keterangan ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Detail Absensi';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }
}
