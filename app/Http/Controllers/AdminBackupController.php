<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Support\StatusBackup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminBackupController extends Controller
{
    // Daftar putih: hanya dua perintah ini yang bisa dijalankan dari halaman
    private const PERINTAH = ['lengkap' => 'backup:lengkap', 'verifikasi' => 'backup:verifikasi'];

    private function daftar(string $dir, string $pola): array
    {
        $berkas = glob($dir.DIRECTORY_SEPARATOR.$pola) ?: [];
        rsort($berkas);

        return array_map(fn ($p) => [
            'nama'   => basename($p),
            'ukuran' => filesize($p),
            'waktu'  => Carbon::createFromTimestamp(filemtime($p)),
        ], $berkas);
    }

    public function index()
    {
        $data   = $this->daftar(storage_path('app/backup'), 'data-*.zip');
        $kunci  = $this->daftar(storage_path('app/backup-kunci'), 'kunci-*.zip');
        $salinan = config('cadangan.salinan_dir');
        $luar   = ($salinan && is_dir($salinan)) ? ($this->daftar($salinan, 'data-*.zip')[0]['nama'] ?? null) : null;

        return view('admin.backup', [
            'status'         => StatusBackup::baca(),
            'berjalan'       => ['lengkap' => StatusBackup::berjalan('lengkap'), 'verifikasi' => StatusBackup::berjalan('verifikasi')],
            'basi'           => StatusBackup::basi(),
            'data'           => $data,
            'kunci'          => $kunci,
            'salinanDiatur'  => (bool) $salinan,
            'salinanTerbaru' => $luar,
            'sandiTerisi'    => strlen((string) config('cadangan.passphrase')) >= 24,
            'simpan'         => (int) config('cadangan.simpan'),
        ]);
    }

    public function jalankan(Request $r)
    {
        $r->validate(['jenis' => 'required|in:lengkap,verifikasi']);
        $jenis = $r->jenis;

        if (StatusBackup::berjalan('lengkap') || StatusBackup::berjalan('verifikasi')) {
            return back()->with('status', 'Masih ada proses backup yang berjalan. Tunggu sampai selesai.');
        }
        if (! function_exists('popen') && ! function_exists('exec')) {
            return back()->withErrors(['jenis' => 'Fungsi menjalankan proses dimatikan di php.ini (disable_functions).']);
        }
        if (strlen((string) config('cadangan.passphrase')) < 24) {
            return back()->withErrors(['jenis' => 'BACKUP_PASSPHRASE di .env belum diisi (minimal 24 karakter).']);
        }

        StatusBackup::minta($jenis);
        LogAktivitas::catat('backup_dimulai', $jenis.' (manual dari panel admin)');
        StatusBackup::lepas(self::PERINTAH[$jenis]);

        // Pastikan prosesnya benar-benar berangkat (perintah menghapus tanda "diminta" saat mulai)
        for ($i = 0; $i < 6; $i++) {
            sleep(1);
            if (empty(StatusBackup::baca()[$jenis]['diminta'])) {
                break;
            }
        }
        if (! empty(StatusBackup::baca()[$jenis]['diminta'])) {
            StatusBackup::gagal($jenis, 'Proses tidak dapat dimulai. Periksa BACKUP_PHP_PATH di .env.');
            return back()->withErrors(['jenis' => 'Proses tidak dapat dimulai. Periksa BACKUP_PHP_PATH di .env.']);
        }

        return back()->with('status', 'Proses dimulai di latar belakang. Halaman ini diperbarui otomatis saat selesai.');
    }

    public function status()
    {
        return response()->json([
            'berjalan' => StatusBackup::berjalan('lengkap') || StatusBackup::berjalan('verifikasi'),
        ]);
    }

    // Unduhan butuh PIN lagi, dan arsipnya tetap terenkripsi
    public function unduh(Request $r)
    {
        $r->validate([
            'berkas' => ['required', 'regex:/^(data|kunci)-\d{8}-\d{6}\.zip$/'],
            'pin'    => 'required|digits:6',
        ], ['pin.digits' => 'PIN terdiri dari 6 angka.']);

        if (! Hash::check($r->pin, (string) $r->user()->pin)) {
            LogAktivitas::catat('pin_salah', 'Unduh backup');
            return back()->withErrors(['pin' => 'PIN salah.']);
        }

        $dir   = str_starts_with($r->berkas, 'kunci-') ? storage_path('app/backup-kunci') : storage_path('app/backup');
        $jalur = $dir.DIRECTORY_SEPARATOR.$r->berkas;
        abort_unless(is_file($jalur), 404);

        LogAktivitas::catat('backup_diunduh', $r->berkas);

        return response()->download($jalur, $r->berkas);
    }
}