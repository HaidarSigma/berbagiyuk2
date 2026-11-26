@extends('admin.layout')
@section('judul', 'Dashboard')
@section('isi')
  @php
    $kartu = [
      ['Menunggu Verifikasi', $menunggu, 'jam', 'bg-yellow-100 text-yellow-700'],
      ['Barang Tersedia', $tersedia, 'paket', 'bg-green-100 text-green-700'],
      ['Barang Ditolak', $ditolak, 'larang', 'bg-red-100 text-red-700'],
      ['Donatur', $donatur, 'hadiah', 'bg-blue-100 text-blue-700'],
      ['Penerima', $penerima, 'pengguna', 'bg-purple-100 text-purple-700'],
    ];
  @endphp

  <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach ($kartu as [$label, $angka, $ic, $warna])
      <div class="bg-white rounded-2xl p-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl {{ $warna }} flex items-center justify-center"><x-ikon :nama="$ic" class="w-5 h-5" /></div>
        <div class="text-2xl font-extrabold mt-3">{{ $angka }}</div>
        <div class="text-xs text-gray-500">{{ $label }}</div>
      </div>
    @endforeach
  </div>

  <div class="bg-white rounded-2xl shadow-sm mt-6 p-5">
    <div class="flex justify-between items-center mb-3">
      <h2 class="font-extrabold">Barang menunggu verifikasi</h2>
      <a href="/admin/barang" class="text-sm text-green-800 font-semibold">Lihat semua →</a>
    </div>
    @forelse ($terbaru as $b)
      <div class="flex justify-between items-center py-3 border-t text-sm">
        <div>
          <div class="font-semibold">{{ $b->judul }}</div>
          <div class="flex flex-wrap items-center gap-1 text-xs text-gray-500">
            <span>{{ $b->kategori->nama }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif ·</span>
            <span class="inline-flex items-center gap-0.5"><x-ikon nama="lokasi" class="w-3.5 h-3.5 text-gray-400" /> {{ $b->lokasi }}</span>
            <span>· oleh {{ $b->user->name }}</span>
          </div>
          @if ($b->donasi?->kebutuhanMendesak)
            <div class="text-xs font-semibold text-orange-800">Kampanye: {{ $b->donasi->kebutuhanMendesak->judul }} — {{ $b->donasi->kebutuhanMendesak->lokasi }}</div>
          @endif
        </div>
        <span class="text-xs text-gray-400">{{ $b->created_at->locale('id')->diffForHumans() }}</span>
      </div>
    @empty
      <p class="text-sm text-gray-500">Tidak ada barang yang menunggu.</p>
    @endforelse
  </div>
@endsection