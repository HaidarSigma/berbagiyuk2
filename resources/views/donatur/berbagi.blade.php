<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bagikan Barang - BerbagiYuk</title>
  @include('partials.head-asset')
  <link rel="stylesheet" href="{{ asset("vendor/leaflet/leaflet.css") }}">
  <script src="{{ asset("vendor/leaflet/leaflet.js") }}"></script>
</head>
<body class="bg-green-50 p-6">
  @php $kunci = array_keys(old('barang', [0 => []])); @endphp

  <p class="max-w-2xl mx-auto mb-4 text-sm text-gray-500">
    Baca dulu <a href="/aturan" target="_blank" rel="noopener" class="text-green-800 font-semibold">Aturan Barang</a> supaya barangmu tidak ditolak.
  </p>

  <form method="POST" action="/berbagi" enctype="multipart/form-data" data-memuat="Mengunggah... jangan tutup halaman" class="max-w-2xl mx-auto bg-white rounded-2xl shadow-sm p-8 space-y-6">
    @csrf
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-green-800">Bagikan Barang</h1>
      <a href="/" class="text-sm text-green-800">← Beranda</a>
    </div>

    @if ($kebutuhanAktif->isNotEmpty())
      <section class="rounded-xl border border-orange-200 bg-orange-50 p-4 space-y-2">
        <label for="kebutuhanMendesak" class="block text-sm font-semibold text-orange-900">Salurkan untuk kampanye bantuan (opsional)</label>
        <select id="kebutuhanMendesak" name="kebutuhan_mendesak_id" data-kebutuhan-campaign class="w-full rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm">
          <option value="">Donasi umum</option>
          @foreach ($kebutuhanAktif as $kebutuhan)
            <option value="{{ $kebutuhan->id }}" data-keterangan="{{ $kebutuhan->keterangan }}" @selected((string) old('kebutuhan_mendesak_id', $kebutuhanTerpilih) === (string) $kebutuhan->id)>
              {{ $kebutuhan->judul }} — {{ $kebutuhan->lokasi }}
            </option>
          @endforeach
        </select>
        <p data-kebutuhan-campaign-info class="hidden text-xs text-orange-900">
        </p>
      </section>
    @endif

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
      <button type="button" data-aksi="tambah" class="w-full border-2 border-dashed border-green-700 text-green-800 rounded-xl py-2 text-sm font-semibold hover:bg-green-50">+ Tambah barang</button>
      <p class="text-xs text-gray-500">Semua barang diambil dari lokasi yang sama. menjadi Maksimal 5 barang per donasi. Tiap barang wajib 3 foto dan 1 video. Semua barang diambil dari lokasi yang sama.</p>
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

    <section class="space-y-2">
      <div data-syarat-penerima-umum>
        <p class="text-sm text-gray-600 mb-1">Boleh diambil oleh (berlaku untuk semua barang)</p>
      <div class="grid grid-cols-2 gap-2 text-sm">
        @foreach (\App\Models\Barang::JENIS as $v => $l)
          <label class="flex items-center gap-2 border rounded-lg px-3 py-2">
            <input type="checkbox" name="syarat_penerima[]" value="{{ $v }}" @checked(in_array($v, old('syarat_penerima', array_keys(\App\Models\Barang::JENIS))))> {{ $l }}
          </label>
        @endforeach
      </div>
      </div>
      <div data-syarat-penerima-korban class="hidden rounded-lg bg-orange-50 p-3 text-sm text-orange-900">
        <input type="hidden" name="syarat_penerima[]" value="korban_bencana" disabled data-syarat-korban-input>
        Barang kampanye ini khusus disalurkan kepada penerima kategori korban bencana.
      </div>
    </section>

    <button class="w-full bg-green-800 text-white rounded-lg py-2.5 font-semibold hover:bg-green-900">Kirim untuk Diverifikasi</button>
  </form>

  <template id="tplBaris">@include('donatur._baris-barang', ['i' => '__I__', 'kategori' => $kategori])</template>

  <script nonce="{{ $cspNonce ?? '' }}">
  let n = {{ max(array_map('intval', $kunci)) + 1 }};
  const daftar = document.getElementById('daftarBarang');
  const nomori = () => daftar.querySelectorAll('.baris-barang .no').forEach((el, i) => el.textContent = i + 1);

  function tambah() {
    if (daftar.children.length >= 5) { alert('Maksimal 5 barang dalam satu donasi.'); return; }
    daftar.insertAdjacentHTML('beforeend', document.getElementById('tplBaris').innerHTML.replaceAll('__I__', n++));
    nomori();
  }
  function hapus(tombol) {
    if (daftar.children.length <= 1) { alert('Minimal satu barang.'); return; }
    tombol.closest('.baris-barang').remove();
    nomori();
  }
  document.querySelector('[data-aksi="tambah"]').addEventListener('click', tambah);
  daftar.addEventListener('click', e => {
    const tombol = e.target.closest('[data-aksi="hapus"]');
    if (tombol) hapus(tombol);
  });
  daftar.addEventListener('invalid', e => {
    const jenis = e.target.matches('[data-kategori-barang]') ? 'kategori'
      : e.target.matches('[data-kondisi-barang]') ? 'kondisi' : null;
    if (!jenis) return;
    const pesan = 'Pilih ' + jenis + ' barang sebelum mengirim.';
    e.target.setCustomValidity(pesan);
    e.target.closest('[data-form-barang]').querySelector('[data-peringatan-' + jenis + ']').classList.remove('hidden');
  }, true);
  daftar.addEventListener('change', e => {
    const jenis = e.target.matches('[data-kategori-barang]') ? 'kategori'
      : e.target.matches('[data-kondisi-barang]') ? 'kondisi' : null;
    if (!jenis) return;
    const peringatan = e.target.closest('[data-form-barang]').querySelector('[data-peringatan-' + jenis + ']');
    if (e.target.value) {
      e.target.setCustomValidity('');
      peringatan.classList.add('hidden');
    } else if (!peringatan.classList.contains('hidden')) {
      e.target.setCustomValidity('Pilih ' + jenis + ' barang sebelum mengirim.');
    }
  });
  nomori();
</script>
@include('partials.peta-pilih', ['lat0' => old('latitude'), 'lng0' => old('longitude')])
@include('donatur._media-js')
@include('partials.interaksi')
</body>
</html>