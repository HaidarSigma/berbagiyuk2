<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verifikasi 2 Langkah - BerbagiYuk</title>
  @include('partials.head-asset')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-sm space-y-4">
    <h1 class="text-2xl font-bold text-green-800">Verifikasi 2 langkah</h1>
    <p class="text-sm text-gray-600">Masukkan kode 6 angka dari aplikasi autentikator. HP hilang? Pakai salah satu kode pemulihan.</p>

    @if (session('status'))<p class="text-sm text-green-700 bg-green-50 rounded-lg p-3">{{ session('status') }}</p>@endif
    @error('kode')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

    @if ($sisa > 0)
      <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">Terlalu banyak percobaan. Coba lagi dalam <b id="hitung">{{ $sisa }}</b> detik.</p>
    @endif

    <form method="POST" action="/totp/verifikasi" class="space-y-3">
      @csrf
      <input name="kode" autocomplete="one-time-code" maxlength="20" placeholder="Kode 6 angka"
             class="w-full border rounded-lg px-3 py-2 text-center text-xl tracking-widest" required autofocus @disabled($sisa > 0)>
      <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold hover:bg-green-900 disabled:bg-gray-300" @disabled($sisa > 0)>Verifikasi</button>
    </form>

    <form method="POST" action="/keluar" class="text-center">@csrf<button class="text-xs text-gray-500">Keluar</button></form>
  </div>

  @if ($sisa > 0)
    <script nonce="{{ $cspNonce ?? '' }}">
      let s = {{ $sisa }};
      const el = document.getElementById('hitung');
      const t = setInterval(() => { s--; if (s <= 0) { clearInterval(t); location.reload(); } else { el.textContent = s; } }, 1000);
    </script>
  @endif
</body>
</html>