<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PinController extends Controller
{
    const BATAS_SALAH = 5;
    const MENIT_KUNCI = 15;

    // Menyiapkan notif selamat datang dan mencatat login yang sudah lengkap
    private function sambut($u, bool $baru = false): void
    {
        $jam = now()->hour;
        $waktu = match (true) {
            $jam >= 4 && $jam < 11  => 'pagi',
            $jam >= 11 && $jam < 15 => 'siang',
            $jam >= 15 && $jam < 18 => 'sore',
            default                 => 'malam',
        };
        $nama = Str::before($u->name, ' ');

        $info = $baru ? 'Akunmu sudah siap dipakai.' : null;
        if (! $baru) {
            $sebelumnya = LogAktivitas::where('user_id', $u->id)->where('aksi', 'login_berhasil')->latest('created_at')->first();
            if ($sebelumnya) {
                $info = 'Terakhir masuk: '.$sebelumnya->created_at->locale('id')->translatedFormat('d M Y, H:i');
            }
        }

        LogAktivitas::catat('login_berhasil', null, $u->id);

        session()->put('selamat', [
            'judul' => $baru ? "Selamat datang di BerbagiYuk, {$nama}!" : "Selamat {$waktu}, {$nama}!",
            'info'  => $info,
        ]);
    }

    public function formBuat()
    {
        return auth()->user()->pin ? redirect('/') : view('auth.pin-buat');
    }

    public function buat(Request $r)
    {
        $r->validate(['pin' => 'required|digits:6|confirmed']);
        $r->user()->forceFill(['pin' => Hash::make($r->pin)])->save();
        $r->session()->put('pin_ok', true);
        $this->sambut($r->user(), true);

        return redirect('/');
    }

    public function formMasuk()
    {
        $u = auth()->user();
        if (! $u->pin) {
            return redirect('/pin/buat');
        }

        $sisa = $u->pin_terkunci_sampai && now()->lt($u->pin_terkunci_sampai)
            ? max(0, $u->pin_terkunci_sampai->timestamp - now()->timestamp)
            : 0;

        return view('auth.pin-masuk', ['sisa' => $sisa]);
    }

    public function masuk(Request $r)
    {
        $r->validate(['pin' => 'required|digits:6']);
        $u = $r->user();

        // Sedang dikunci: tidak diproses sama sekali
        if ($u->pin_terkunci_sampai && now()->lt($u->pin_terkunci_sampai)) {
            LogAktivitas::catat('pin_terkunci', 'Percobaan saat akun terkunci');
            return redirect('/pin/masuk');
        }

        if (Hash::check($r->pin, $u->pin)) {
            $u->forceFill(['pin_percobaan' => 0, 'pin_terkunci_sampai' => null])->save();
            $r->session()->put('pin_ok', true);
            $this->sambut($u);

            return redirect('/');
        }

        $u->pin_percobaan++;
        $terkunci = $u->pin_percobaan >= self::BATAS_SALAH;

        if ($terkunci) {
            $u->pin_percobaan = 0;
            $u->pin_terkunci_sampai = now()->addMinutes(self::MENIT_KUNCI);
        }
        $u->save();

        LogAktivitas::catat($terkunci ? 'pin_terkunci' : 'pin_salah', $terkunci ? 'Dikunci '.self::MENIT_KUNCI.' menit' : null);

        if ($terkunci) {
            return redirect('/pin/masuk');
        }

        $sisa = self::BATAS_SALAH - $u->pin_percobaan;

        return back()->withErrors(['pin' => "PIN salah. Sisa percobaan: {$sisa}."]);
    }
}