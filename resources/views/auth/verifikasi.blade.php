@extends('auth.layout')
@section('judul', 'Verifikasi Email')
@section('isi')
  <h1 class="text-2xl font-bold text-green-800">Cek emailmu</h1>
  <p class="text-sm text-gray-600">
    @if (session('email_error'))
      Email verifikasi belum berhasil dikirim ke <b>{{ auth()->user()->email }}</b>. Klik tombol di bawah untuk mencoba lagi.
    @else
      Kami mengirim tautan verifikasi ke <b>{{ auth()->user()->email }}</b>. Klik tautan itu untuk melanjutkan.
    @endif
  </p>
  @if (session('status')) <p class="text-sm text-green-700">{{ session('status') }}</p> @endif
  @if (session('email_error')) <p class="text-sm text-red-600">{{ session('email_error') }}</p> @endif
  <form method="POST" action="/email/kirim-ulang">@csrf
    <button class="w-full border-2 border-green-800 text-green-800 rounded-lg py-2 font-semibold">Kirim ulang email</button>
  </form>
  <form method="POST" action="/keluar">@csrf<button class="text-sm text-gray-500">Keluar</button></form>
@endsection