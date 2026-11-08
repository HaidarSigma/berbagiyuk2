<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\User;
use App\Rules\PasswordKuat;
use Illuminate\Http\Request;

class AdminPenggunaController extends Controller
{
    private function bisaDiatur(User $u): void
    {
        abort_if($u->role === 'admin' || $u->id === auth()->id(), 403);
    }

    public function index(Request $r)
    {
        $pengguna = User::query()
            ->when($r->role, fn ($q, $v) => $q->where('role', $v))
            ->when($r->q, fn ($q, $k) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$k}%")
                ->orWhere('email', 'like', "%{$k}%")))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.pengguna', ['pengguna' => $pengguna]);
    }

    public function nonaktifkan(Request $r, User $user)
    {
        $this->bisaDiatur($user);
        $data = $r->validate(['alasan_nonaktif' => 'required|string|max:200']);

        $user->forceFill(['aktif' => false, 'alasan_nonaktif' => $data['alasan_nonaktif']])->save();
        LogAktivitas::catat('akun_dinonaktifkan', $user->email.' | '.$data['alasan_nonaktif']);

        return back()->with('status', "Akun {$user->name} dinonaktifkan.");
    }

    public function aktifkan(User $user)
    {
        $this->bisaDiatur($user);

        $user->forceFill(['aktif' => true, 'alasan_nonaktif' => null])->save();
        LogAktivitas::catat('akun_diaktifkan', $user->email);

        return back()->with('status', "Akun {$user->name} diaktifkan kembali.");
    }

    public function buatPetugas(Request $r)
    {
        $data = $r->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'no_hp'    => 'required|string|max:20',
            'password' => ['required', new PasswordKuat],
        ]);

        User::forceCreate([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'no_hp'             => $data['no_hp'],
            'password'          => bcrypt($data['password']),
            'role'              => 'petugas',
            'terverifikasi'     => true,
            'email_verified_at' => now(),
            'password_changed_at' => now()->subMonths(3)->subDay(),
        ]);
        LogAktivitas::catat('petugas_dibuat', $data['email']);

        return back()->with('status', 'Akun petugas dibuat. Petugas akan diminta membuat PIN saat login pertama.');
    }
}