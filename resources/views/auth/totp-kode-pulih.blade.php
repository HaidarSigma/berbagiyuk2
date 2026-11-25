<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kode Pemulihan - BerbagiYuk</title>
  @include('partials.head-asset')
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-sm p-8 w-full max-w-md space-y-4">
    <h1 class="text-2xl font-bold text-green-800">Simpan kode pemulihan</h1>
    <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3">Kode ini <b>hanya tampil sekali</b>. Tiap kode bisa dipakai satu kali bila HP hilang. Cetak atau simpan di tempat aman di luar HP-mu.</p>

    <div class="grid grid-cols-2 gap-2">
      @foreach ($kode as $k)
        <code class="bg-gray-100 rounded px-2 py-1.5 text-center font-mono text-sm select-all">{{ $k }}</code>
      @endforeach
    </div>

    <div class="flex gap-2">
      <button type="button" data-cetak class="flex-1 border rounded-lg py-2 text-sm text-gray-700">Cetak</button>
      <a href="/" class="flex-1 bg-green-800 text-white rounded-lg py-2 text-sm text-center font-semibold hover:bg-green-900">Saya sudah menyimpan</a>
    </div>
  </div>
</body>
</html>