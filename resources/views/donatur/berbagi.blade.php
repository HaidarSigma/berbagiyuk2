<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bagikan Barang - BerbagiYuk</title>
  <p class="text-sm text-gray-500 -mt-4">Baca dulu <a href="/aturan" target="_blank" class="text-green-800 font-semibold">Aturan Barang</a> supaya barangmu tidak ditolak.</p>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body class="bg-gray-50 p-6">
  @php $kunci = array_keys(old('barang', [0 => []])); @endphp

  <form method="POST" action="/berbagi" enctype="multipart/form-data" class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm p-8 space-y-6">
    @csrf
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-green-800">Bagikan Barang</h1>
      <a href="/" class="text-sm text-green-800">← Beranda</a>
    </div>

    @if ($errors->any())
      <div class="text-sm text-red-600 bg-red-50 rounded-lg p-3 space-y-0.5">
        @foreach (array_unique($errors->all()) as $e)<p>{{ $e }}</p>@endforeach
      </div>
    @endif

    <section class="space-y-3">
      <h2 class="font-extrabold">Barang yang dibagikan</h2>
      <div id="daftarBarang" class="space-y-3">
        @foreach ($kunci as $i)
          @include('donatur._baris-barang', ['i' => $i, 'kategori' => $kategori])
        @endforeach
      </div>
      <button type="button" onclick="tambah()" class="w-full border-2 border-dashed border-green-700 text-green-800 rounded-xl py-2 text-sm font-semibold hover:bg-green-50">+ Tambah barang</button>
      <p class="text-xs text-gray-500">Maksimal 10 barang dalam satu donasi. Semua barang diambil dari lokasi yang sama.</p>
    </section>

    <section class="space-y-3">
      <h2 class="font-extrabold">Lokasi pengambilan</h2>
      <div>
        <label class="text-sm text-gray-600">Area (tampil di publik)</label>
        <input name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: {{ config('wilayah.contoh') }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
      </div>
      <div>
        <p class="text-sm text-gray-600 mb-1">Klik peta untuk menandai titik pengambilan (hanya dilihat admin dan petugas)</p>
        <div id="map" class="h-64 rounded-lg z-0"></div>
        <input type="hidden" name="latitude" id="lat" value="{{ old('latitude') }}">
        <input type="hidden" name="longitude" id="lng" value="{{ old('longitude') }}">
      </div>
    </section>

    <section>
      <p class="text-sm text-gray-600 mb-1">Boleh diambil oleh (berlaku untuk semua barang)</p>
      <div class="grid grid-cols-2 gap-2 text-sm">
        @foreach (\App\Models\Barang::JENIS as $v => $l)
          <label class="flex items-center gap-2 border rounded-lg px-3 py-2">
            <input type="checkbox" name="syarat_penerima[]" value="{{ $v }}" @checked(in_array($v, old('syarat_penerima', array_keys(\App\Models\Barang::JENIS))))> {{ $l }}
          </label>
        @endforeach
      </div>
    </section>

    <button class="w-full bg-green-800 text-white rounded-lg py-2.5 font-semibold hover:bg-green-900">Kirim untuk Diverifikasi</button>
  </form>

  <template id="tplBaris">@include('donatur._baris-barang', ['i' => '__I__', 'kategori' => $kategori])</template>

  <script>
  let n = {{ max(array_map('intval', $kunci)) + 1 }};
  const daftar = document.getElementById('daftarBarang');
  const nomori = () => daftar.querySelectorAll('.baris-barang .no').forEach((el, i) => el.textContent = i + 1);

  function tambah() {
    if (daftar.children.length >= 10) { alert('Maksimal 10 barang dalam satu donasi.'); return; }
    daftar.insertAdjacentHTML('beforeend', document.getElementById('tplBaris').innerHTML.replaceAll('__I__', n++));
    nomori();
  }
  function hapus(tombol) {
    if (daftar.children.length <= 1) { alert('Minimal satu barang.'); return; }
    tombol.closest('.baris-barang').remove();
    nomori();
  }
  nomori();
</script>
@include('partials.peta-pilih', ['lat0' => old('latitude'), 'lng0' => old('longitude')])
</body>
</html>