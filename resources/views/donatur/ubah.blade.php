<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ubah Barang - BerbagiYuk</title>
  @include('partials.head-asset')
  <link rel="stylesheet" href="{{ asset("vendor/leaflet/leaflet.css") }}">
  <script src="{{ asset("vendor/leaflet/leaflet.js") }}"></script>
</head>
<body class="bg-gray-50 p-6">
  <form method="POST" action="/barang-saya/{{ $barang->id }}/ubah" enctype="multipart/form-data" class="max-w-xl mx-auto bg-white rounded-2xl shadow-sm p-8 space-y-4">
    @csrf
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-green-800">Ubah Barang</h1>
      <a href="/barang-saya" class="text-sm text-green-800">← Barang Saya</a>
    </div>

    @if ($barang->verifikasi === 'ditolak' && $barang->alasan_tolak)
      <div class="bg-red-50 text-red-700 text-sm rounded-lg p-3">
        <b>Ditolak admin:</b> {{ $barang->alasan_tolak }}<br>
        Perbaiki bagian yang dimaksud, lalu simpan untuk mengajukan ulang.
      </div>
    @endif

    @if ($errors->any())
      <div class="text-sm text-red-600">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
    @endif

    <input name="judul" value="{{ old('judul', $barang->judul) }}" placeholder="Nama barang" class="w-full border rounded-lg px-3 py-2" required>

    <div class="grid grid-cols-2 gap-3">
      <select name="kategori_id" class="border rounded-lg px-3 py-2" required>
        @foreach ($kategori as $k)
          <option value="{{ $k->id }}" @selected(old('kategori_id', $barang->kategori_id) == $k->id)>{{ $k->nama }}</option>
        @endforeach
      </select>
      <select name="kondisi" class="border rounded-lg px-3 py-2" required>
        @foreach (['Baru', 'Layak Pakai', 'Perlu Perbaikan'] as $kd)
          <option @selected(old('kondisi', $barang->kondisi) == $kd)>{{ $kd }}</option>
        @endforeach
      </select>
    </div>

    <textarea name="deskripsi" rows="3" placeholder="Deskripsi barang" class="w-full border rounded-lg px-3 py-2" required>{{ old('deskripsi', $barang->deskripsi) }}</textarea>

    <div class="space-y-4">
  <div>
    <div class="text-sm font-semibold">Foto barang</div>
    <div class="flex gap-2 mt-1">
      @forelse ($barang->fotos as $m)
        <img src="{{ asset('storage/'.$m->path) }}" class="w-20 h-20 object-cover rounded-lg border" alt="">
      @empty
        @if ($barang->foto)<img src="{{ asset('storage/'.$barang->foto) }}" class="w-20 h-20 object-cover rounded-lg border" alt="">@endif
      @endforelse
    </div>
    <p class="text-xs text-gray-500 mt-2">Untuk mengganti foto, pilih <b>ketiga</b> foto baru (wajib 3). Kosongkan semuanya kalau tidak ingin mengganti.</p>
    <div class="grid grid-cols-3 gap-2 mt-1">
      @foreach (['Tampak depan', 'Tampak samping / detail', 'Kondisi / kekurangan'] as $n => $petunjuk)
        <label class="slot-foto block border-2 border-dashed rounded-lg p-2 text-center bg-white">
          <img class="hidden w-full h-20 object-cover rounded mb-1" alt="">
          <span class="block text-[11px] text-gray-500">{{ $n + 1 }}. {{ $petunjuk }}</span>
          <input type="file" name="foto[{{ $n }}]" accept="image/*" class="in-foto block w-full text-[11px] mt-1">
        </label>
      @endforeach
    </div>
  </div>

  <div class="slot-video">
    <div class="text-sm font-semibold">Video barang</div>
    @if ($barang->video)
      <video controls preload="metadata" class="w-full max-h-48 rounded-lg bg-black mt-1"><source src="/media/video/{{ $barang->video->id }}"></video>
      <p class="text-xs text-gray-500 mt-1">Pilih video baru di bawah hanya kalau ingin menggantinya.</p>
    @else
      <p class="text-xs text-yellow-700 bg-yellow-50 rounded p-2 mt-1">Barang ini belum punya video. Sebaiknya tambahkan satu.</p>
    @endif
    <input type="file" name="video" accept="video/*" class="in-video w-full text-sm mt-1">
    <p class="info-video text-xs text-green-700 mt-1"></p>
  </div>
</div>

    <div>
      <label class="text-sm text-gray-600">Area (tampil di publik)</label>
      <input name="lokasi" value="{{ old('lokasi', $barang->lokasi) }}" placeholder="Contoh: {{ config('wilayah.contoh') }}" class="w-full border rounded-lg px-3 py-2 mt-1" required>
    </div>

    <div>
      <p class="text-sm text-gray-600 mb-1">Klik peta untuk menandai titik pengambilan (hanya dilihat admin dan petugas)</p>
      <div id="map" class="h-64 rounded-lg z-0"></div>
      <input type="hidden" name="latitude" id="lat" value="{{ old('latitude', $barang->latitude) }}">
      <input type="hidden" name="longitude" id="lng" value="{{ old('longitude', $barang->longitude) }}">
    </div>

        <div>
      <p class="text-sm text-gray-600 mb-1">Boleh diambil oleh</p>
      <div class="grid grid-cols-2 gap-2 text-sm">
        @foreach (\App\Models\Barang::JENIS as $v => $l)
          <label class="flex items-center gap-2 border rounded-lg px-3 py-2">
            <input type="checkbox" name="syarat_penerima[]" value="{{ $v }}" @checked(in_array($v, old('syarat_penerima', $barang->syarat())))> {{ $l }}
          </label>
        @endforeach
      </div>
    </div>

    <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold">Simpan &amp; Ajukan Ulang</button>
  </form>

  @include('partials.peta-pilih', ['lat0' => old('latitude', $barang->latitude), 'lng0' => old('longitude', $barang->longitude)])
  @include('donatur._media-js')
@include('partials.interaksi')
</body>
</html>