<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Unggahan terlalu besar - BerbagiYuk</title>
  @include('partials.head-asset')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-sm text-center space-y-4">
    <div class="w-14 h-14 mx-auto rounded-2xl bg-green-100 text-green-700 flex items-center justify-center"><x-ikon nama="paket" class="w-8 h-8" /></div>
    <h1 class="text-xl font-bold text-green-800">Unggahan terlalu besar</h1>
    <p class="text-sm text-gray-600">Total ukuran foto dan video melebihi batas. Kirim lebih sedikit barang sekaligus, atau pendekkan dan kompres videonya.</p>
    <a href="#" data-kembali class="block bg-green-800 text-white rounded-lg py-2 font-semibold">Kembali</a>
  </div>
@include('partials.interaksi')
</body>
</html>