<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PinController extends Controller
{
    const BATAS_SALAH = 5;
    const MENIT_KUNCI = 15;

    public function formBuat()
    {
        return auth()->user()->pin ? redirect('/') : view('auth.pin-buat');
    }

    public function buat(Request $r)
    {
        $r->validate(['pin' => 'required|digits:6|confirmed']);
        $r->user()->forceFill(['pin' => Hash::make($r->pin)])->save();
        $r->session()->put('pin_ok', true);

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