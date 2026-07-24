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

class AbsensiTahunanExport implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected int $tahun;
    protected ?string $divisi;

    protected static array $BULAN = [
        1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
        7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des',
    ];

    public function __construct(int $tahun, ?string $divisi = null)
    {
        $this->tahun  = $tahun;
        $this->divisi = $divisi;
    }

    /**
     * Hitung hari kerja (Senin-Jumat) dalam satu bulan, tidak melebihi hari ini
     */
    private function hitungHariKerja(int $tahun, int $bulan): int
    {
        $awal   = Carbon::create($tahun, $bulan, 1);
        $akhir  = $awal->copy()->endOfMonth();
        $batas  = $akhir->lt(now()) ? $akhir : now();
        $count  = 0;
        $tgl    = $awal->copy();
        while ($tgl->lte($batas)) {
            if (!$tgl->isWeekend()) $count++;
            $tgl->addDay();
        }
        return $count;
    }

    public function array(): array
    {
        $karyawans = User::where('role', 'karyawan')
            ->when($this->divisi, fn($q) => $q->where('divisi', $this->divisi))
            ->orderBy('name')
            ->get();

        $rows = [];
        $no   = 1;

        foreach ($karyawans as $k) {
            $row    = [$no++, $k->nip ?? '-', $k->name, $k->divisi ?? '-'];
            $totalH = $totalI = $totalA = $totalT = 0;

            for ($m = 1; $m <= 12; $m++) {
                $abs = Absensi::where('user_id', $k->id)
                    ->whereMonth('tanggal', $m)
                    ->whereYear('tanggal', $this->tahun)
                    ->get();

                $h  = $abs->where('status', 'hadir')->count();
                $i  = $abs->whereIn('status', ['izin', 'sakit'])->count();
                $t  = $abs->where('keterangan', 'Terlambat')->count();

                // Alpha = hari kerja - semua absensi tercatat
                $hk = $this->hitungHariKerja($this->tahun, $m);
                $a  = max(0, $hk - $abs->count());

                $row[] = $h > 0 ? $h : '';
                $row[] = $i > 0 ? $i : '';
                $row[] = $a > 0 ? $a : '';

                $totalH += $h;
                $totalI += $i;
                $totalA += $a;
                $totalT += $t;
            }

            $row[] = $totalH;
            $row[] = $totalI;
            $row[] = $totalA;
            $row[] = $totalT;

            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        $head = ['No', 'NIP', 'Nama Karyawan', 'Divisi'];
        foreach (self::$BULAN as $lbl) {
            $head[] = "H-$lbl";
            $head[] = "I-$lbl";
            $head[] = "A-$lbl";
        }
        $head[] = 'Total Hadir';
        $head[] = 'Total Izin';
        $head[] = 'Total Alpha';
        $head[] = 'Total Terlambat';
        return $head;
    }

    public function title(): string
    {
        return "Rekap Tahunan {$this->tahun}";
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = count($this->array()) + 1;

        // Header biru
        $sheet->getStyle('A1:AP1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a5f']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Warna kolom Total di akhir (4 kolom terakhir)
        if ($lastRow > 1) {
            // Total Hadir (AM) - hijau
            $sheet->getStyle("AM2:AM{$lastRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '155724']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D4EDDA']],
            ]);
            // Total Izin (AN) - kuning
            $sheet->getStyle("AN2:AN{$lastRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '856404']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3CD']],
            ]);
            // Total Alpha (AO) - merah
            $sheet->getStyle("AO2:AO{$lastRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '721C24']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8D7DA']],
            ]);
            // Total Terlambat (AP) - oranye
            $sheet->getStyle("AP2:AP{$lastRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => '7D4000']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FDEBD0']],
            ]);

            // Center semua kolom angka
            $sheet->getStyle("E2:AP{$lastRow}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Freeze panes agar nama karyawan tidak ikut scroll
        $sheet->freezePane('E2');

        return [];
    }
}
