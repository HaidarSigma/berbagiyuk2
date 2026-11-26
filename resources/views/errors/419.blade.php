<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Halaman kedaluwarsa - BerbagiYuk</title>
  @include('partials.head-asset')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-sm text-center space-y-4">
    <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center"><x-ikon nama="jam" class="w-8 h-8" /></div>
    <h1 class="text-xl font-bold text-green-800">Halaman sudah kedaluwarsa</h1>
    <p class="text-sm text-gray-600">Sesi atau token formulir sudah tidak berlaku. Kembali ke beranda, buka ulang formulir, lalu coba lagi.</p>
    <a href="/" class="block bg-green-800 text-white rounded-lg py-2 font-semibold">Ke beranda</a>
  </div>
@include('partials.interaksi')
</body>
</html>