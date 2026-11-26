<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aktifkan Verifikasi 2 Langkah - BerbagiYuk</title>
  @include('partials.head-asset')
  <script src="{{ asset('vendor/qrcodejs/qrcode.min.js') }}"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-md space-y-4">
    <h1 class="text-2xl font-bold text-green-800">Aktifkan verifikasi 2 langkah</h1>
    <p class="text-sm text-gray-600">Akun {{ auth()->user()->role === 'admin' ? 'admin' : 'petugas' }} wajib memakai kode dari aplikasi autentikator (Google Authenticator, Microsoft Authenticator, Aegis, atau sejenisnya).</p>

    <ol class="text-sm text-gray-700 list-decimal ml-5 space-y-1">
      <li>Pasang aplikasi autentikator di HP.</li>
      <li>Pindai kode QR di bawah, atau ketik kunci manualnya.</li>
      <li>Masukkan kode 6 angka yang muncul di aplikasi.</li>
    </ol>

    <div id="qr" class="flex justify-center"></div>
    <div class="text-center text-xs text-gray-500">
      Kunci manual:<br>
      <code class="select-all break-all bg-gray-100 rounded px-2 py-1 font-mono text-sm">{{ trim(chunk_split($rahasia, 4, ' ')) }}</code>
    </div>

    <form method="POST" action="/totp/aktifkan" class="space-y-3">
      @csrf
      @error('kode')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
      <input name="kode" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="Kode 6 angka"
             class="w-full border rounded-lg px-3 py-2 text-center text-xl tracking-widest" required autofocus>
      <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold hover:bg-green-900">Aktifkan</button>
    </form>

    <form method="POST" action="/keluar" class="text-center">@csrf<button class="text-xs text-gray-500">Keluar</button></form>
  </div>

  <script nonce="{{ $cspNonce ?? '' }}">
    new QRCode(document.getElementById('qr'), { text: @json($uri), width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
  </script>
  @include('partials.tombol-tema', ['melayang' => true])
</body>
</html>