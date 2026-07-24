<?php

namespace App\Exports;

use App\Models\Absensi;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AbsensiRingkasanSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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

    public function array(): array
    {
        // Hitung hari kerja bulan ini
        $awal      = Carbon::create($this->tahun, $this->bulan, 1);
        $akhir     = $awal->copy()->endOfMonth();
        $batas     = $akhir->lt(now()) ? $akhir : now();
        $hariKerja = 0;
        $tgl = $awal->copy();
        while ($tgl->lte($batas)) {
            if (!$tgl->isWeekend()) $hariKerja++;
            $tgl->addDay();
        }

        $karyawans = User::where('role', 'karyawan')
            ->when($this->userId, fn($q) => $q->where('id', $this->userId))
            ->get();

        $rows = [];
        $no   = 1;

        foreach ($karyawans as $k) {
            $abs = Absensi::where('user_id', $k->id)
                ->whereMonth('tanggal', $this->bulan)
                ->whereYear('tanggal', $this->tahun)
                ->get();

            $hadir  = $abs->where('status', 'hadir')->count();
            $izin   = $abs->where('status', 'izin')->count();
            $sakit  = $abs->where('status', 'sakit')->count();
            $alpha  = max(0, $hariKerja - $abs->count());
            $telat  = $abs->where('keterangan', 'Terlambat')->count();
            $persen = $hariKerja > 0 ? round($hadir / $hariKerja * 100) . '%' : '0%';

            $rows[] = [
                $no++,
                $k->nip      ?? '-',
                $k->name,
                $k->jabatan  ?? '-',
                $k->divisi   ?? '-',
                $hadir,
                $izin,
                $sakit,
                $alpha,
                $telat,
                $hariKerja,
                $persen,
            ];
        }

        return $rows;
    }

    public function headings(): array
    {
        $namaBulan = Carbon::create(null, $this->bulan)->translatedFormat('F');
        return [
            'No',
            'NIP',
            'Nama Karyawan',
            'Jabatan',
            'Divisi',
            "Hadir ({$namaBulan})",
            "Izin ({$namaBulan})",
            "Sakit ({$namaBulan})",
            "Alpha ({$namaBulan})",
            "Terlambat ({$namaBulan})",
            'Hari Kerja',
            '% Kehadiran',
        ];
    }

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function styles(Worksheet $sheet): array
    {
        // Warna kolom hadir = hijau, izin = kuning, sakit = biru, alpha = merah
        $lastRow = count($this->array()) + 1;

        // Header biru
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Warna kolom statistik (baris data)
        if ($lastRow > 1) {
            // Hadir (F)
            $sheet->getStyle("F2:F{$lastRow}")->getFont()->getColor()->setARGB('00FF157A'); // hijau
            $sheet->getStyle("F2:F{$lastRow}")->getFont()->setBold(true);
            // Izin (G)
            $sheet->getStyle("G2:G{$lastRow}")->getFont()->getColor()->setARGB('00E67E22');
            // Sakit (H)
            $sheet->getStyle("H2:H{$lastRow}")->getFont()->getColor()->setARGB('000C5460');
            // Alpha (I) - merah tebal
            $sheet->getStyle("I2:I{$lastRow}")->getFont()->getColor()->setARGB('00DC3545');
            $sheet->getStyle("I2:I{$lastRow}")->getFont()->setBold(true);
        }

        // Center semua kolom angka
        $sheet->getStyle("F1:L{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        return [];
    }
}
