@extends('petugas.layout')
@section('judul', 'Pengiriman')
@section('isi')
  @php
    $wa    = fn ($no) => 'https://wa.me/'.preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $no));
    $peta  = fn ($lat, $lng) => $lat ? 'https://www.google.com/maps?q='.$lat.','.$lng : null;
    $jenis = \App\Models\Barang::JENIS;
    $posko = config('posko');
  @endphp

  <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-sm mb-5 space-y-1">
    <div class="font-semibold text-blue-900">📦 Barang diambil dari {{ $posko['nama'] }}</div>
    <div class="text-blue-800">{{ $posko['alamat'] }}</div>
    @if ($peta($posko['latitude'], $posko['longitude']))
      <a target="_blank" href="{{ $peta($posko['latitude'], $posko['longitude']) }}" class="inline-block font-semibold text-blue-700">🗺️ Peta posko</a>
    @endif
  </div>

  <h2 class="font-extrabold mb-2">Pengirimanku</h2>
  @forelse ($saya as $t)
    @php $b = $t->permintaan->barang; $n = $t->permintaan->penerima; @endphp
    <div class="bg-white rounded-2xl shadow-sm p-4 mb-4 space-y-3">
      <div class="flex justify-between items-start gap-3">
        <div>
          <div class="font-extrabold">{{ $b->judul }}</div>
          <div class="text-xs text-gray-500">{{ $b->kategori->nama }} · {{ $b->kondisi }} · Tugas #{{ $t->id }}</div>
        </div>
        <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-800 whitespace-nowrap">Menuju penerima</span>
      </div>

      <div class="rounded-xl border p-3 text-sm space-y-1">
        <div class="text-xs font-semibold text-gray-500">ANTAR KE PENERIMA</div>
        <div class="font-semibold">{{ $n->name }} <span class="text-xs font-normal text-gray-500">({{ $jenis[$n->jenis_penerima] ?? '-' }})</span></div>
        <div class="text-gray-600">{{ $n->alamat }}</div>
        <div class="flex flex-wrap gap-3 pt-1 font-semibold text-blue-700">
          @if ($peta($n->latitude, $n->longitude))<a target="_blank" href="{{ $peta($n->latitude, $n->longitude) }}">🗺️ Peta</a>@endif
          <a href="tel:{{ $n->no_hp }}">📞 Telepon</a>
          <a target="_blank" href="{{ $wa($n->no_hp) }}">💬 WhatsApp</a>
        </div>
      </div>

      <form method="POST" action="/petugas/pengiriman/{{ $t->id }}/selesai" enctype="multipart/form-data" class="space-y-2">
        @csrf
        <label class="text-sm font-semibold">Kode serah terima (minta dari penerima)</label>
        <input name="kode" inputmode="numeric" maxlength="6" placeholder="6 angka" class="w-full border rounded-lg px-3 py-2 tracking-widest text-center text-lg" required>
        <label class="text-sm font-semibold">Foto bukti serah terima</label>
        <input type="file" name="foto" accept="image/*" capture="environment" class="w-full text-sm" required>
        <input name="catatan" maxlength="300" placeholder="Catatan (opsional)" class="w-full border rounded-lg px-3 py-2 text-sm">
        <button class="w-full bg-green-700 text-white rounded-xl py-2.5 font-semibold hover:bg-green-800">Selesaikan Pengantaran</button>
      </form>

      <form method="POST" action="/petugas/pengiriman/{{ $t->id }}/lepas" data-konfirmasi="Lepas tugas ini?">@csrf
        <button class="text-xs text-red-600">Lepas tugas</button>
      </form>
    </div>
  @empty
    <p class="text-sm text-gray-500 mb-4">Belum ada pengiriman yang kamu ambil.</p>
  @endforelse

  <h2 class="font-extrabold mb-2 mt-6">Tersedia untuk diantar</h2>
  <div class="space-y-3">
    @forelse ($tersedia as $t)
      @php $b = $t->permintaan->barang; $n = $t->permintaan->penerima; $km = $t->jarakKm(); @endphp
      <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-1">
          <div class="font-bold">{{ $b->judul }}</div>
          <div class="text-xs text-gray-500">{{ $b->kategori->nama }} · {{ $b->kondisi }}</div>
          <div class="text-sm text-gray-700">🏠 Antar ke <b>{{ \Illuminate\Support\Str::before($n->name, ' ') }}</b> ({{ $jenis[$n->jenis_penerima] ?? '-' }})</div>
          @if ($km !== null)<div class="text-xs text-gray-500">Jarak dari posko sekitar {{ $km }} km</div>@endif
        </div>
        <form method="POST" action="/petugas/pengiriman/{{ $t->id }}/ambil">@csrf
          <button class="w-full sm:w-auto bg-blue-700 text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-blue-800">Ambil Tugas</button>
        </form>
      </div>
    @empty
      <p class="text-sm text-gray-500">Belum ada pengiriman baru. Tugas muncul setelah admin menyetujui permintaan penerima.</p>
    @endforelse
  </div>
@endsection