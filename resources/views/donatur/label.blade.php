<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Label {{ $barang->judul }} - BerbagiYuk</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
  <style>@media print { .no-print { display: none !important; } body { background: #fff; } }</style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col items-center justify-center p-4 gap-4">
  <div class="bg-white border-2 border-dashed border-gray-400 rounded-xl p-6 w-80 text-center space-y-3">
    <div class="text-sm font-extrabold text-green-800">BerbagiYuk</div>
    <div id="qr" class="flex justify-center"></div>
    <div class="font-bold">{{ $barang->judul }}</div>
    <div class="text-xs text-gray-500">{{ $barang->kategori->nama }}</div>
    <code class="block text-xs break-all bg-gray-100 rounded p-2">{{ $barang->token }}</code>
    <div class="text-[11px] text-gray-500">Tempel label ini pada barang. Petugas akan memindainya saat pengambilan.</div>
  </div>

  <div class="no-print flex gap-3">
    <button onclick="window.print()" class="bg-green-800 text-white rounded-lg px-5 py-2 font-semibold">Cetak</button>
    <a href="/barang-saya" class="border rounded-lg px-5 py-2 text-gray-600">Kembali</a>
  </div>

  <script>
    new QRCode(document.getElementById('qr'), { text: @json($barang->token), width: 180, height: 180, correctLevel: QRCode.CorrectLevel.M });
  </script>
</body>
</html>