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
     * Export rekap absensi ke PDF
     */
    public function pdf(Request $request)
    {
        $bulan    = $request->input('bulan', now()->month);
        $tahun    = $request->input('tahun', now()->year);
        $userId   = $request->input('user_id');
        $karyawan = $userId ? User::find($userId) : null;

        $absensis = Absensi::with('user')
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->when($userId, fn($q) => $q->where('user_id', $userId))
            ->orderBy('tanggal')
            ->orderBy('user_id')
            ->get();

        $namaFile = 'rekap-absensi-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . $tahun . '.pdf';

        $pdf = Pdf::loadView('admin.rekap.pdf', compact('absensis', 'bulan', 'tahun', 'karyawan'))
            ->setPaper('a4', 'landscape');

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
