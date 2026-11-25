<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Support\Totp;
use Illuminate\Http\Request;

class TotpController extends Controller
{
    const BATAS_SALAH = 5;
    const MENIT_KUNCI = 15;

    private function khususStaf(Request $r): void
    {
        abort_unless(in_array($r->user()->role, ['admin', 'petugas'], true), 403);
    }

    // 8 kode pemulihan sekali pakai; yang disimpan hanya hash-nya
    private function buatKodePulih(): array
    {
        $tampil = $hash = [];
        for ($i = 0; $i < 8; $i++) {
            $mentah = '';
            for ($j = 0; $j < 10; $j++) {
                $mentah .= Totp::ALFABET[random_int(0, 31)];
            }
            $tampil[] = substr($mentah, 0, 5).'-'.substr($mentah, 5);
            $hash[]   = hash_hmac('sha256', $mentah, config('app.key'));
        }

        return [$tampil, $hash];
    }

    public function formAktifkan(Request $r)
    {
        $this->khususStaf($r);
        $u = $r->user();
        if ($u->totp_aktif) {
            return redirect('/');
        }

        if (! $r->session()->has('totp_rahasia_baru')) {
            $r->session()->put('totp_rahasia_baru', Totp::buatRahasia());
        }
        $rahasia = $r->session()->get('totp_rahasia_baru');

        return response()->view('auth.totp-aktifkan', ['rahasia' => $rahasia, 'uri' => Totp::uri($rahasia, $u->email)])
            ->header('Cache-Control', 'no-store');
    }

    public function aktifkan(Request $r)
    {
        $this->khususStaf($r);
        $u = $r->user();
        abort_if($u->totp_aktif, 403);

        $r->validate(['kode' => 'required|digits:6'], ['kode.digits' => 'Kode terdiri dari 6 angka.']);

        $rahasia = $r->session()->get('totp_rahasia_baru');
        if (! $rahasia) {
            return redirect('/totp/aktifkan');
        }

        $langkah = Totp::periksa($rahasia, $r->kode);
        if ($langkah === null) {
            LogAktivitas::catat('totp_salah', 'Saat pendaftaran');
            return back()->withErrors(['kode' => 'Kode salah. Pastikan jam HP akurat, lalu masukkan kode terbaru.']);
        }

        [$tampil, $hash] = $this->buatKodePulih();

        $u->forceFill([
            'totp_rahasia'      => $rahasia,
            'totp_aktif'        => true,
            'totp_terakhir'     => $langkah,
            'totp_kode_pulih'   => $hash,
            'totp_gagal'        => 0,
            'totp_kunci_sampai' => null,
        ])->save();

        $r->session()->forget('totp_rahasia_baru');
        $u->putusSesi(true);                       // sesi lain diputus, sesi ini dipertahankan
        $r->session()->put('totp_ok', true);
        $r->session()->flash('kode_pulih', $tampil);
        LogAktivitas::catat('totp_diaktifkan');

        return redirect('/totp/kode-pulih');
    }

    public function kodePulih(Request $r)
    {
        $kode = $r->session()->get('kode_pulih');
        if (! $kode) {
            return redirect('/');
        }

        return response()->view('auth.totp-kode-pulih', ['kode' => $kode])->header('Cache-Control', 'no-store');
    }

    public function formVerifikasi(Request $r)
    {
        $u = $r->user();
        if (! $u->totp_aktif) {
            return redirect('/totp/aktifkan');
        }
        if ($r->session()->get('totp_ok')) {
            return redirect('/');
        }

        $sisa = $u->totp_kunci_sampai && now()->lt($u->totp_kunci_sampai)
            ? $u->totp_kunci_sampai->timestamp - now()->timestamp
            : 0;

        return view('auth.totp-verifikasi', ['sisa' => max(0, $sisa)]);
    }

    public function verifikasi(Request $r)
    {
        $u = $r->user();
        abort_unless($u->totp_aktif, 403);
        $r->validate(['kode' => 'required|string|max:20']);

        if ($u->totp_kunci_sampai && now()->lt($u->totp_kunci_sampai)) {
            LogAktivitas::catat('totp_terkunci', 'Percobaan saat terkunci');
            return redirect('/totp/verifikasi');
        }

        $bersih     = preg_replace('/\s+/', '', (string) $r->kode);
        $berhasil   = false;
        $pakaiPulih = false;

        if (preg_match('/^\d{6}$/', $bersih)) {
            $langkah = Totp::periksa((string) $u->totp_rahasia, $bersih);
            if ($langkah !== null && $langkah > (int) $u->totp_terakhir) {   // kode yang sama tidak bisa dipakai dua kali
                $u->totp_terakhir = $langkah;
                $berhasil = true;
            }
        } else {
            $hash   = hash_hmac('sha256', strtoupper(str_replace('-', '', $bersih)), config('app.key'));
            $daftar = (array) ($u->totp_kode_pulih ?? []);
            foreach ($daftar as $i => $h) {
                if (hash_equals($h, $hash)) {
                    unset($daftar[$i]);
                    $u->totp_kode_pulih = array_values($daftar);
                    $berhasil = $pakaiPulih = true;
                    break;
                }
            }
        }

        if ($berhasil) {
            $u->totp_gagal = 0;
            $u->totp_kunci_sampai = null;
            $u->save();

            $r->session()->put('totp_ok', true);
            $r->session()->regenerate();
            LogAktivitas::catat($pakaiPulih ? 'totp_pulih_dipakai' : 'totp_berhasil');

            $pesan = $pakaiPulih
                ? 'Kode pemulihan dipakai. Sisa '.count((array) $u->totp_kode_pulih).' kode. Hubungi atasan bila hampir habis.'
                : null;

            return redirect('/')->with($pesan ? ['status' => $pesan] : []);
        }

        $u->totp_gagal++;
        $terkunci = $u->totp_gagal >= self::BATAS_SALAH;
        if ($terkunci) {
            $u->totp_gagal = 0;
            $u->totp_kunci_sampai = now()->addMinutes(self::MENIT_KUNCI);
        }
        $u->save();

        LogAktivitas::catat($terkunci ? 'totp_terkunci' : 'totp_salah', $terkunci ? 'Dikunci '.self::MENIT_KUNCI.' menit' : null);

        if ($terkunci) {
            return redirect('/totp/verifikasi');
        }

        return back()->withErrors(['kode' => 'Kode salah. Sisa percobaan: '.(self::BATAS_SALAH - $u->totp_gagal).'.']);
    }
}