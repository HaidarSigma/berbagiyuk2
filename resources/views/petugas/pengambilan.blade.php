@extends('petugas.layout')
@section('judul', 'Pengambilan')
@section('isi')
  @php
    $wa   = fn ($no) => 'https://wa.me/'.preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $no));
    $peta = fn ($lat, $lng) => $lat ? 'https://www.google.com/maps?q='.$lat.','.$lng : null;
  @endphp

  <h2 class="font-extrabold mb-2">Menunggu diambil <span class="text-sm font-normal text-gray-500">(tugasku)</span></h2>

  @forelse ($kelompok as $grup)
    @php $b0 = $grup->first()->permintaan->barang; $d = $b0->user; @endphp
    <div class="bg-white rounded-2xl shadow-sm p-4 mb-4 space-y-3">
      <div class="text-sm space-y-1">
        <div class="text-xs font-semibold text-gray-500">AMBIL DARI DONATUR @if ($b0->donasi_id) · DONASI #{{ $b0->donasi_id }} @endif</div>
        <div class="font-semibold">{{ $d->name }}</div>
        <div class="text-gray-600">{{ $d->alamat }}</div>
        <div class="text-xs text-gray-500">Area: {{ $b0->lokasi }}</div>
        <div class="flex flex-wrap gap-3 pt-1 font-semibold text-blue-700">
          @if ($peta($b0->latitude, $b0->longitude))<a target="_blank" href="{{ $peta($b0->latitude, $b0->longitude) }}">🗺️ Peta</a>@endif
          <a href="tel:{{ $d->no_hp }}">📞 Telepon</a>
          <a target="_blank" href="{{ $wa($d->no_hp) }}">💬 WhatsApp</a>
        </div>
      </div>

      <form method="POST" action="/petugas/pengambilan/diambil" enctype="multipart/form-data" class="space-y-2">
        @csrf
        <div class="text-sm font-semibold">Barang yang diambil (hilangkan centang kalau tidak jadi)</div>
        @foreach ($grup as $t)
          @php $b = $t->permintaan->barang; @endphp
          <label class="flex items-start gap-2 border rounded-lg p-2 text-sm">
            <input type="checkbox" name="tugas_id[]" value="{{ $t->id }}" checked class="mt-1">
            <span><b>{{ $b->judul }}</b> <span class="text-xs text-gray-500">· {{ $b->kategori->nama }} · {{ $b->kondisi }}</span></span>
          </label>
        @endforeach

        <label class="text-sm font-semibold block">Foto bukti pengambilan</label>
        <input type="file" name="foto" accept="image/*" capture="environment" class="w-full text-sm" required>
        <button class="w-full bg-blue-700 text-white rounded-xl py-2.5 font-semibold hover:bg-blue-800">Tandai Sudah Diambil</button>
      </form>

      <div class="flex flex-wrap gap-3 text-xs">
        @foreach ($grup as $t)
          <form method="POST" action="/petugas/tugas/{{ $t->id }}/lepas" onsubmit="return confirm('Lepas tugas ini?')">@csrf
            <button class="text-red-600">Lepas: {{ \Illuminate\Support\Str::limit($t->permintaan->barang->judul, 22) }}</button>
          </form>
        @endforeach
      </div>
    </div>
  @empty
    <p class="text-sm text-gray-500 mb-4">Belum ada tugas yang menunggu diambil. Ambil dari daftar di bawah.</p>
  @endforelse

  <h2 class="font-extrabold mb-2 mt-6">Tugas tersedia</h2>
  <div class="space-y-3">
    @forelse ($tersedia as $t)
      @php $b = $t->permintaan->barang; $n = $t->permintaan->penerima; $km = $t->jarakKm(); @endphp
      <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-1">
          <div class="font-bold">{{ $b->judul }} @if ($b->donasi_id)<span class="text-[11px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Donasi #{{ $b->donasi_id }}</span>@endif</div>
          <div class="text-xs text-gray-500">{{ $b->kategori->nama }} · {{ $b->kondisi }}</div>
          <div class="text-sm text-gray-700">📍 Ambil di <b>{{ $b->lokasi }}</b></div>
          <div class="text-sm text-gray-700">🏠 Antar ke <b>{{ \Illuminate\Support\Str::before($n->name, ' ') }}</b> ({{ \App\Models\Barang::JENIS[$n->jenis_penerima] ?? '-' }})</div>
          @if ($km !== null)<div class="text-xs text-gray-500">Jarak antar-titik sekitar {{ $km }} km</div>@endif
        </div>
        <form method="POST" action="/petugas/tugas/{{ $t->id }}/ambil">@csrf
          <button class="w-full sm:w-auto bg-blue-700 text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-blue-800">Ambil Tugas</button>
        </form>
      </div>
    @empty
      <p class="text-sm text-gray-500">Belum ada tugas baru. Tugas muncul setelah admin menyetujui permintaan.</p>
    @endforelse
  </div>
@endsection