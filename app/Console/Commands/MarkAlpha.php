<?php

namespace App\Console\Commands;

use App\Models\Absensi;
use App\Models\Izin;
use App\Models\PengaturanKantor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class MarkAlpha extends Command
{
    protected $signature   = 'absensi:mark-alpha {--date= : Tanggal (Y-m-d), default hari ini}';
    protected $description = 'Tandai karyawan yang tidak absen sebagai Alpha setelah jam batas terlambat';

    public function handle(): int
    {
        $tanggal = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : now();

        // Tidak proses hari libur (Sabtu/Minggu)
        if ($tanggal->isWeekend()) {
            $this->info('Hari ini hari libur, tidak ada proses alpha.');
            return 0;
        }

        $kantor      = PengaturanKantor::first();
        $batasTelat  = $kantor?->jam_masuk_batas ?? '10:00:00';

        // Hanya jalankan setelah jam batas terlambat
        if (now()->format('H:i:s') < $batasTelat) {
            $this->warn('Belum melewati jam batas terlambat (' . $batasTelat . '). Proses dibatalkan.');
            return 0;
        }

        $karyawans = User::where('role', 'karyawan')->get();
        $count     = 0;

        foreach ($karyawans as $karyawan) {
            // Cek sudah absen hari ini
            $sudahAbsen = Absensi::where('user_id', $karyawan->id)
                ->whereDate('tanggal', $tanggal)
                ->exists();

            if ($sudahAbsen) continue;

            // Cek apakah ada izin disetujui hari ini
            $adaIzin = Izin::where('user_id', $karyawan->id)
                ->where('status', 'disetujui')
                ->where('tanggal_mulai', '<=', $tanggal->format('Y-m-d'))
                ->where('tanggal_selesai', '>=', $tanggal->format('Y-m-d'))
                ->exists();

            if ($adaIzin) continue;

            // Tandai alpha
            Absensi::create([
                'user_id'    => $karyawan->id,
                'tanggal'    => $tanggal->format('Y-m-d'),
                'status'     => 'alpha',
                'keterangan' => 'Otomatis – tidak hadir',
            ]);

            $count++;
            $this->line("Alpha: {$karyawan->name}");
        }

        $this->info("Selesai. {$count} karyawan ditandai Alpha.");
        return 0;
    }
}
