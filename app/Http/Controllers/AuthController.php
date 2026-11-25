<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Support\Wilayah;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthController extends Controller
{
    public function formDaftar() { return view('auth.daftar'); }
    public function formMasuk()  { return view('auth.masuk'); }

    public function daftar(Request $r)
    {
        $data = $r->validate([
            'name'           => 'required|string|max:100',
            'email'          => 'required|email|unique:users',
            'password'       => ['required', 'confirmed', new \App\Rules\PasswordKuat],
            'role'           => 'required|in:donatur,penerima',
            'no_hp'          => 'required|string|max:20',
            'alamat'         => 'required|string',
            ...Wilayah::aturan(),
            'jenis_penerima' => 'required_if:role,penerima|nullable|in:keluarga_kurang_mampu,panti_yayasan,korban_bencana,pelajar',
            'dokumen'        => 'required_if:role,penerima|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ], [
            'email.unique' => 'Email ini sudah digunakan. Coba masuk dengan akun tersebut; jika belum diverifikasi, kamu bisa mengirim ulang tautannya.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);
        $data['password'] = bcrypt($data['password']);
        $data['password_changed_at'] = now();

        if ($data['role'] === 'penerima') {
        $nama = 'dokumen/'.\Illuminate\Support\Str::uuid().'.'.$r->file('dokumen')->extension().'.enc';
        \Illuminate\Support\Facades\Storage::put($nama, \Illuminate\Support\Facades\Crypt::encrypt(file_get_contents($r->file('dokumen')->getRealPath())));
$data['dokumen'] = $nama;
            $data['status_penerima'] = 'menunggu';
        } else {
            unset($data['jenis_penerima'], $data['dokumen']);
        }

        $user = User::create($data);
        Auth::login($user);
        $emailTerkirim = $this->kirimEmailVerifikasi($user, true);

        $response = redirect()->route('verification.notice');
        return $emailTerkirim
            ? $response
            : $response->with('email_error', 'Akunmu sudah dibuat, tetapi email verifikasi belum berhasil dikirim. Periksa koneksi email lalu coba kirim ulang.');
    }

    public function masuk(Request $r)
    {
        $ingatSaya = $r->boolean('ingat_saya');

        if (Auth::attempt($r->only('email', 'password'), $ingatSaya)) {
            if (! Auth::user()->aktif) {
                \App\Models\LogAktivitas::catat('login_ditolak_nonaktif', 'Email: '.$r->email);
                Auth::logout();
                $r->session()->invalidate();
                $r->session()->regenerateToken();
                return back()->withErrors(['email' => 'Akun ini dinonaktifkan. Hubungi admin.'])->onlyInput('email');
            }

            $r->session()->regenerate();
            $r->session()->forget('pin_ok');
            \App\Models\LogAktivitas::catat('login');

            $user = Auth::user();
            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            return redirect($user->pin ? '/pin/masuk' : '/pin/buat');
        }

        \App\Models\LogAktivitas::catat('login_gagal', 'Email: '.$r->email);
        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function kirimUlangVerifikasi(Request $r)
    {
        $terkirim = $this->kirimEmailVerifikasi($r->user());

        return $terkirim
            ? back()->with('status', 'Email verifikasi dikirim ulang.')
            : back()->with('email_error', 'Email belum berhasil dikirim. Periksa koneksi email lalu coba lagi.');
    }

    private function kirimEmailVerifikasi(User $user, bool $baruTerdaftar = false): bool
    {
        try {
            if ($baruTerdaftar) {
                event(new Registered($user));
            } else {
                $user->sendEmailVerificationNotification();
            }

            return true;
        } catch (TransportExceptionInterface $e) {
            Log::warning('Gagal mengirim email verifikasi.', [
                'user_id' => $user->id,
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function keluar(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();
        return redirect('/');
    }
}