<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Rules\PasswordKuat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class KedaluwarsaController extends Controller
{
    public function form()
    {
        return auth()->user()->passwordKedaluwarsa() ? view('auth.password-kedaluwarsa') : redirect('/');
    }

    public function simpan(Request $r)
    {
        $r->validate([
            'password_lama' => 'required',
            'password'      => ['required', 'confirmed', 'different:password_lama', new PasswordKuat],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        $u = $r->user();

        if (! Hash::check($r->password_lama, $u->password)) {
            LogAktivitas::catat('password_gagal_diganti', 'Saat password kedaluwarsa');
            return back()->withErrors(['password_lama' => 'Password lama salah.']);
        }

        $u->forceFill(['password' => bcrypt($r->password), 'password_changed_at' => now()])->save();
        LogAktivitas::catat('password_diganti', 'Password kedaluwarsa diganti');

        return redirect('/')->with('status', 'Password berhasil diperbarui.');
    }
}