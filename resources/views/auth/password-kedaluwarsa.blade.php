@extends('auth.layout')
@section('judul', 'Password Kedaluwarsa')
@section('isi')
  <h1 class="text-2xl font-bold text-green-800">Buat Password Baru</h1>
  <div class="bg-yellow-50 text-yellow-800 text-sm rounded-lg p-3">
    Masa berlaku password sudah habis (3 bulan). Demi keamanan akunmu, buat password baru untuk melanjutkan.
  </div>
  @if ($errors->any())
    <div class="text-sm text-red-600">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
  @endif
  <form method="POST" action="/password/kedaluwarsa" class="space-y-3">@csrf
    <input type="password" name="password_lama" placeholder="Password lama" class="w-full border rounded-lg px-3 py-2" required>
    <input type="password" name="password" placeholder="Password baru (min. 12 karakter)" class="w-full border rounded-lg px-3 py-2" required>
    @include('partials.syarat-password')
    <input type="password" name="password_confirmation" placeholder="Ulangi password baru" class="w-full border rounded-lg px-3 py-2" required>
    <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold">Simpan Password Baru</button>
  </form>
  <form method="POST" action="/keluar">@csrf<button class="text-sm text-gray-500">Keluar</button></form>
@endsection