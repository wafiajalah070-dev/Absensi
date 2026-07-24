<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AbsensiExport;
use App\Exports\AbsensiTahunanExport;
use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    /**
     * Export rekap absensi ke Excel
     */
    public function excel(Request $request)
    {
        $bulan    = $request->input('bulan', now()->month);
        $tahun    = $request->input('tahun', now()->year);
        $userId   = $request->input('user_id');
        $namaFile = 'rekap-absensi-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun . '.xlsx';

        return Excel::download(new AbsensiExport($bulan, $tahun, $userId), $namaFile);
    }

    /**
     * Export rekap absensi ke PDF — ringkasan per karyawan
     */
    public function pdf(Request $request)
    {
        $bulan    = $request->input('bulan', now()->month);
        $tahun    = $request->input('tahun', now()->year);
        $userId   = $request->input('user_id');
        $karyawan = $userId ? User::find($userId) : null;
        $namaFile = 'rekap-absensi-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun . '.pdf';

        if ($karyawan) {
            // PDF detail 1 karyawan — per hari
            $absensis = Absensi::where('user_id', $karyawan->id)
                ->whereMonth('tanggal', $bulan)
                ->whereYear('tanggal', $tahun)
                ->orderBy('tanggal')
                ->get();

            $jumlahHari = cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun);

            $pdf = Pdf::loadView('admin.rekap.pdf', compact('absensis','bulan','tahun','karyawan','jumlahHari'))
                ->setPaper('a4', 'landscape');
        } else {
            // PDF ringkasan semua karyawan — hitung alpha dari hari kerja
            $awal  = \Carbon\Carbon::create($tahun, $bulan, 1);
            $akhir = $awal->copy()->endOfMonth();
            $batas = $akhir->lt(now()) ? $akhir : now();
            $hariKerja = 0;
            $tgl = $awal->copy();
            while ($tgl->lte($batas)) {
                if (!$tgl->isWeekend()) $hariKerja++;
                $tgl->addDay();
            }

            $karyawans = User::where('role', 'karyawan')->get()->map(function ($k) use ($bulan, $tahun, $hariKerja) {
                $abs   = Absensi::where('user_id', $k->id)
                    ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)->get();
                $hadir = $abs->where('status','hadir')->count();
                $izin  = $abs->whereIn('status',['izin','sakit'])->count();
                $alpha = max(0, $hariKerja - $abs->count());
                $telat = $abs->where('keterangan','Terlambat')->count();
                $persen = $hariKerja > 0 ? round($hadir / $hariKerja * 100) : 0;
                return compact('k','hadir','izin','alpha','telat','persen');
            });

            $pdf = Pdf::loadView('admin.rekap.pdf-ringkasan', compact('karyawans','bulan','tahun','hariKerja'))
                ->setPaper('a4', 'portrait');
        }

        return $pdf->download($namaFile);
    }

    /**
     * Export rekap tahunan ke Excel
     */
    public function excelTahunan(Request $request)
    {
        $tahun    = $request->input('tahun', now()->year);
        $divisi   = $request->input('divisi');
        $namaFile = "rekap-tahunan-{$tahun}.xlsx";

        return Excel::download(new AbsensiTahunanExport($tahun, $divisi), $namaFile);
    }

    /**
     * Export rekap tahunan ke PDF
     */
    public function pdfTahunan(Request $request)
    {
        $tahun  = $request->input('tahun', now()->year);
        $divisi = $request->input('divisi');

        $karyawans = User::where('role', 'karyawan')
            ->when($divisi, fn($q) => $q->where('divisi', $divisi))
            ->get();

        // Siapkan data per karyawan per bulan
        $data = [];
        foreach ($karyawans as $k) {
            $rekapBulan = [];
            $totalH = $totalI = $totalA = 0;
            for ($m = 1; $m <= 12; $m++) {
                $abs = Absensi::where('user_id', $k->id)
                    ->whereMonth('tanggal', $m)->whereYear('tanggal', $tahun)->get();
                $h = $abs->where('status','hadir')->count();
                $i = $abs->whereIn('status',['izin','sakit'])->count();
                $a = $abs->where('status','alpha')->count();
                $rekapBulan[$m] = compact('h','i','a');
                $totalH += $h; $totalI += $i; $totalA += $a;
            }
            $data[] = compact('k','rekapBulan','totalH','totalI','totalA');
        }

        $pdf = Pdf::loadView('admin.rekap.pdf-tahunan', compact('data','tahun','divisi'))
            ->setPaper('a4', 'landscape');

        return $pdf->download("rekap-tahunan-{$tahun}.pdf");
    }
}
