<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Rules\PasswordKuat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Models\Barang;
use App\Models\ResetKode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PengaturanController extends Controller
{
    public function index()
    {
        return view('pengaturan', ['u' => auth()->user()]);
    }

    public function profil(Request $r)
    {
        $u      = $r->user();
        $lokasi = in_array($u->role, ['donatur', 'penerima']);
        $wajib  = $lokasi ? 'required' : 'nullable';

        $data = $r->validateWithBag('profil', [
            'name'      => 'required|string|max:100',
            'no_hp'     => 'required|string|max:20',
            'alamat'    => $wajib.'|string',
            'latitude'  => $wajib.'|numeric',
            'longitude' => $wajib.'|numeric',
        ]);

        if (! $lokasi) {
            unset($data['alamat'], $data['latitude'], $data['longitude']);
        }

        $u->update($data);
        LogAktivitas::catat('profil_diubah');

        return back()->with('status', 'Profil berhasil diperbarui.')->with('bagian', 'profil');
    }

    public function password(Request $r)
    {
        $r->validateWithBag('password', [
            'password_lama' => 'required',
            'password'      => ['required', 'confirmed', 'different:password_lama', new PasswordKuat],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.different' => 'Password baru harus berbeda dari password lama.',
        ]);

        $u = $r->user();

        if (! Hash::check($r->password_lama, $u->password)) {
            LogAktivitas::catat('password_gagal_diganti');
            return back()->withErrors(['password_lama' => 'Password lama salah.'], 'password');
        }

        $u->forceFill(['password' => bcrypt($r->password), 'password_changed_at' => now()])->save();
        LogAktivitas::catat('password_diganti');

        try {
            Mail::raw('Password akun BerbagiYuk kamu baru saja diubah. Jika bukan kamu yang melakukannya, segera hubungi admin.',
                fn ($m) => $m->to($u->email)->subject('Keamanan akun BerbagiYuk'));
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('status', 'Password berhasil diganti.')->with('bagian', 'password');
    }

    public function pin(Request $r)
    {
        $r->validateWithBag('pin', [
            'pin_lama' => 'required|digits:6',
            'pin'      => 'required|digits:6|confirmed|different:pin_lama',
        ], [
            'pin.confirmed' => 'Konfirmasi PIN tidak cocok.',
            'pin.different' => 'PIN baru harus berbeda dari PIN lama.',
        ]);

        $u = $r->user();

        if (! Hash::check($r->pin_lama, $u->pin)) {
            LogAktivitas::catat('pin_salah', 'Saat mengganti PIN');
            return back()->withErrors(['pin_lama' => 'PIN lama salah.'], 'pin');
        }

        $u->forceFill(['pin' => Hash::make($r->pin), 'pin_percobaan' => 0, 'pin_terkunci_sampai' => null])->save();
        LogAktivitas::catat('pin_diganti');

        return back()->with('status', 'PIN berhasil diganti.')->with('bagian', 'pin');
    }
    public function foto(Request $r)
{
    $r->validateWithBag('foto', [
        'foto' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    ], [
        'foto.required' => 'Pilih foto terlebih dahulu.',
        'foto.image'    => 'Berkas harus berupa gambar.',
        'foto.mimes'    => 'Format foto harus JPG, PNG, atau WEBP.',
        'foto.max'      => 'Ukuran foto maksimal 2 MB.',
    ]);

    $u = $r->user();
    if ($u->foto_profil) {
        Storage::disk('public')->delete($u->foto_profil);
    }

    $u->forceFill(['foto_profil' => $r->file('foto')->store('profil', 'public')])->save();
    LogAktivitas::catat('foto_profil_diganti');

    return back()->with('status', 'Foto profil diperbarui.')->with('bagian', 'foto');
}

public function hapusFoto(Request $r)
{
    $u = $r->user();
    if ($u->foto_profil) {
        Storage::disk('public')->delete($u->foto_profil);
    }

    $u->forceFill(['foto_profil' => null])->save();
    LogAktivitas::catat('foto_profil_dihapus');

    return back()->with('status', 'Foto profil dihapus.')->with('bagian', 'foto');
}

public function hapusAkun(Request $r)
{
    $u = $r->user();
    abort_unless(in_array($u->role, ['donatur', 'penerima']), 403);

    $r->validateWithBag('hapus', [
        'password_hapus' => 'required',
        'pin_hapus'      => 'required|digits:6',
        'konfirmasi'     => 'required|in:HAPUS',
    ], [
        'konfirmasi.in'       => 'Ketik HAPUS (huruf besar) untuk mengonfirmasi.',
        'password_hapus.required' => 'Password wajib diisi.',
        'pin_hapus.required'  => 'PIN wajib diisi.',
        'pin_hapus.digits'    => 'PIN harus 6 angka.',
    ]);

    if (! Hash::check($r->password_hapus, $u->password) || ! Hash::check($r->pin_hapus, $u->pin)) {
        LogAktivitas::catat('hapus_akun_gagal', 'Password atau PIN salah');
        return back()->withErrors(['password_hapus' => 'Password atau PIN salah.'], 'hapus');
    }

    if ($alasan = $u->penghalangHapusAkun()) {
        return back()->withErrors(['password_hapus' => $alasan], 'hapus');
    }

    // Kumpulkan berkas yang harus ikut dihapus sebelum barisnya hilang
    $berkasPublik = Barang::where('user_id', $u->id)->pluck('foto')->filter()->all();
    if ($u->foto_profil) {
        $berkasPublik[] = $u->foto_profil;
    }
    $dokumen = $u->dokumen;
    $id      = $u->id;
    $peran   = $u->role;
    $email   = $u->email;

    DB::transaction(function () use ($u, $email) {
        $u->tokens()->delete();
        DB::table('password_reset_tokens')->where('email', $email)->delete();
        ResetKode::where('email', $email)->delete();
        // Barang, permintaan, dan relasi lain ikut terhapus lewat cascade di database.
        $u->delete();
    });

    Storage::disk('public')->delete($berkasPublik);
    if ($dokumen) {
        Storage::disk('local')->delete($dokumen);
    }

    try {
        Mail::raw('Akun BerbagiYuk kamu telah dihapus atas permintaanmu sendiri. Jika bukan kamu yang melakukannya, segera hubungi admin.',
            fn ($m) => $m->to($email)->subject('Akun BerbagiYuk dihapus'));
    } catch (\Throwable $e) {
        report($e);
    }

    Auth::logout();
    $r->session()->invalidate();
    $r->session()->regenerateToken();

    LogAktivitas::catat('akun_dihapus', "ID {$id} | {$peran}");

    return redirect('/')->with('status', 'Akunmu telah dihapus.');
}
}