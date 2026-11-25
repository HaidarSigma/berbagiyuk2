<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\Tugas;
use App\Models\User;
use Illuminate\Http\Request;

class AdminPengirimanController extends Controller
{
    private function validasi(Request $r): void
    {
        $r->validate([
            'petugas' => 'nullable|integer',
            'dari'    => 'nullable|date',
            'sampai'  => 'nullable|date',
            'q'       => 'nullable|string|max:100',
        ]);
    }

    private function kueri(Request $r, string $tab)
    {
        $tgl = $tab === 'selesai' ? 'selesai_at' : 'created_at';

        return Tugas::with('permintaan.barang.kategori', 'permintaan.barang.user', 'permintaan.penerima', 'petugas')
            ->when($tab === 'selesai', fn ($q) => $q->where('status', 'selesai'), fn ($q) => $q->where('status', '!=', 'selesai'))
            ->when($r->filled('petugas'), fn ($q) => $q->where('petugas_id', $r->petugas))
            ->when($r->filled('dari'), fn ($q) => $q->whereDate($tgl, '>=', $r->dari))
            ->when($r->filled('sampai'), fn ($q) => $q->whereDate($tgl, '<=', $r->sampai))
            ->when($r->filled('q'), function ($q) use ($r) {
                $k = $r->q;
                $q->where(fn ($w) => $w
                    ->whereHas('permintaan.barang', fn ($b) => $b->where('judul', 'like', "%{$k}%"))
                    ->orWhereHas('permintaan.barang.user', fn ($u) => $u->where('name', 'like', "%{$k}%"))
                    ->orWhereHas('permintaan.penerima', fn ($u) => $u->where('name', 'like', "%{$k}%")));
            })
            ->latest($tgl);
    }

    public function index(Request $r)
    {
        $this->validasi($r);
        $tab     = $r->query('tab') === 'berlangsung' ? 'berlangsung' : 'selesai';
        $selesai = fn () => Tugas::where('status', 'selesai');

        return view('admin.pengiriman', [
            'tab'         => $tab,
            'daftar'      => $this->kueri($r, $tab)->paginate(20)->withQueryString(),
            'petugas'     => User::where('role', 'petugas')->orderBy('name')->get(['id', 'name']),
            'total'       => $selesai()->count(),
            'bulanIni'    => $selesai()->whereMonth('selesai_at', now()->month)->whereYear('selesai_at', now()->year)->count(),
            'rata'        => $selesai()->whereNotNull('ditugaskan_at')
                                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, ditugaskan_at, selesai_at)) as m')->value('m'),
            'berlangsung' => Tugas::where('status', '!=', 'selesai')->count(),
        ]);
    }

    public function ekspor(Request $r)
    {
        $this->validasi($r);
        LogAktivitas::catat('riwayat_diekspor', 'Riwayat pengiriman selesai');

        // Cegah "CSV injection": sel yang diawali = + - @ diberi tanda petik
        $aman = fn ($v) => preg_match('/^[=+\-@\t\r]/', (string) $v) ? "'".$v : $v;
        $nama = 'riwayat-pengiriman-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($r, $aman) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");   // supaya Excel membaca UTF-8
            fputcsv($out, ['ID Tugas', 'Selesai', 'Barang', 'Kategori', 'Donatur', 'Penerima', 'Petugas', 'Ditugaskan', 'Diambil', 'Durasi (menit)', 'Catatan']);

            foreach ($this->kueri($r, 'selesai')->take(5000)->get() as $t) {
                $b = $t->permintaan->barang;

                fputcsv($out, array_map($aman, [
                    $t->id,
                    optional($t->selesai_at)->format('Y-m-d H:i'),
                    $b->judul,
                    $b->kategori->nama,
                    $b->user->name,
                    $t->permintaan->penerima->name,
                    $t->petugas->name ?? '-',
                    optional($t->ditugaskan_at)->format('Y-m-d H:i'),
                    optional($t->diambil_at)->format('Y-m-d H:i'),
                    ($t->ditugaskan_at && $t->selesai_at) ? $t->ditugaskan_at->diffInMinutes($t->selesai_at) : '',
                    $t->catatan,
                ]));
            }
            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}