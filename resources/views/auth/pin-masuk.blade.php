@extends('auth.layout')
@section('judul', 'Masukkan PIN')
@section('isi')
  <h1 class="text-2xl font-bold text-green-800">Masukkan PIN</h1>

  @if ($sisa > 0)
    <div class="bg-red-50 text-red-700 text-sm rounded-lg p-3 space-y-1">
      <div class="font-semibold">Akun dikunci sementara</div>
      <div>Terlalu banyak PIN yang salah. Coba lagi dalam <b id="hitung">--:--</b>.</div>
    </div>
  @endif

  @error('pin') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

  <form method="POST" action="/pin/masuk" class="space-y-3">@csrf
    <input type="password" name="pin" inputmode="numeric" maxlength="6" placeholder="PIN 6 angka" class="w-full border rounded-lg px-3 py-2 tracking-widest disabled:bg-gray-100" required autofocus @disabled($sisa > 0)>
    <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold disabled:bg-gray-300" @disabled($sisa > 0)>Lanjut</button>
  </form>

  <a href="/lupa?tujuan=pin" class="block text-sm text-green-800">Lupa PIN?</a>
  <form method="POST" action="/keluar">@csrf<button class="text-sm text-gray-500">Keluar</button></form>

  @if ($sisa > 0)
    <script>
      let sisa = {{ $sisa }};
      const hitung = document.getElementById('hitung');
      const fmt = s => Math.floor(s / 60) + ':' + String(s % 60).padStart(2, '0');
      hitung.textContent = fmt(sisa);
      const t = setInterval(() => {
        sisa--;
        hitung.textContent = fmt(Math.max(sisa, 0));
        if (sisa <= 0) { clearInterval(t); location.reload(); }
      }, 1000);
    </script>
  @endif
@endsection