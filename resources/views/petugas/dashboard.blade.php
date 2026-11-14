@extends('petugas.layout')
@section('judul', 'Beranda Petugas')
@section('isi')
  @php
    $kartu = [
      ['Tugas Tersedia', $tersedia, '📋', 'bg-yellow-100', '/petugas/pengambilan'],
      ['Menunggu Diambil', $menunggu, '📦', 'bg-orange-100', '/petugas/pengambilan'],
      ['Siap Diantar', $antar, '🚚', 'bg-blue-100', '/petugas/pengiriman'],
      ['Selesai Bulan Ini', $bulanIni, '✅', 'bg-green-100', '/petugas/riwayat?tab=pengiriman'],
    ];
  @endphp

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
    @foreach ($kartu as [$label, $angka, $ic, $warna, $url])
      <a href="{{ $url }}" class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl {{ $warna }} flex items-center justify-center text-xl">{{ $ic }}</div>
        <div class="text-2xl font-extrabold mt-3">{{ $angka }}</div>
        <div class="text-xs text-gray-500">{{ $label }}</div>
      </a>
    @endforeach
  </div>

  <div class="bg-white rounded-2xl shadow-sm mt-5 p-4">
    <div class="flex justify-between items-center mb-2">
      <h2 class="font-extrabold">Siap diantar</h2>
      <a href="/petugas/pengiriman" class="text-sm text-blue-700 font-semibold">Lihat semua →</a>
    </div>
    @forelse ($siapAntar as $t)
      <div class="flex justify-between items-center py-3 border-t text-sm">
        <div>
          <div class="font-semibold">{{ $t->permintaan->barang->judul }}</div>
          <div class="text-xs text-gray-500">Untuk {{ $t->permintaan->penerima->name }}</div>
        </div>
        <span class="text-xs text-gray-400">diambil {{ $t->diambil_at->locale('id')->diffForHumans() }}</span>
      </div>
    @empty
      <p class="text-sm text-gray-500">Belum ada barang yang siap diantar.</p>
    @endforelse
  </div>
@endsection