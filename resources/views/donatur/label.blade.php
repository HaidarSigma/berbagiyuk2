<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Label {{ $judul }} - BerbagiYuk</title>
  @include('partials.head-asset')
  <script src="{{ asset("vendor/qrcodejs/qrcode.min.js") }}"></script>
  <style>@media print { .no-print { display: none !important; } body { background: #fff; } .label { break-inside: avoid; } }</style>
</head>
<body class="bg-gray-50 p-4">
  <div class="no-print max-w-3xl mx-auto flex flex-wrap items-center justify-between gap-3 mb-4">
    <div>
      <h1 class="font-bold text-green-800">Label QR: {{ $judul }}</h1>
      <p class="text-xs text-gray-500">Cetak, gunting, lalu tempel satu label pada tiap barang. Petugas akan memindainya saat pengambilan.</p>
    </div>
    <div class="flex gap-2">
      <button data-cetak class="bg-green-800 text-white rounded-lg px-5 py-2 font-semibold">Cetak</button>
      <a href="/barang-saya" class="border rounded-lg px-5 py-2 text-gray-600">Kembali</a>
    </div>
  </div>

  <div class="max-w-3xl mx-auto grid grid-cols-2 sm:grid-cols-3 gap-4">
    @foreach ($barangs as $b)
      <div class="label bg-white border-2 border-dashed border-gray-400 rounded-xl p-4 text-center space-y-2">
        <div class="text-xs font-extrabold text-green-800">BerbagiYuk</div>
        <div id="qr-{{ $b->id }}" class="flex justify-center"></div>
        <div class="font-bold text-sm leading-tight">{{ $b->judul }}</div>
        <div class="text-[11px] text-gray-500">{{ $b->kategori->nama }}</div>
        <code class="block text-[10px] break-all bg-gray-100 rounded p-1">{{ $b->token }}</code>
      </div>
    @endforeach
  </div>

  <script nonce="{{ $cspNonce ?? '' }}">
    @foreach ($barangs as $b)
      new QRCode(document.getElementById('qr-{{ $b->id }}'), { text: @json($b->token), width: 130, height: 130, correctLevel: QRCode.CorrectLevel.M });
    @endforeach
  </script>
@include('partials.interaksi')
</body>
</html>