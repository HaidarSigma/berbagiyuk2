@extends('petugas.layout')
@section('judul', 'Beranda Petugas')
@section('isi')
  @php
    $kartu = [
      ['Pengambilan tersedia', $ambilBaru, '📦', 'bg-yellow-100', '/petugas/pengambilan'],
      ['Pengambilanku', $ambilSaya, '🧺', 'bg-orange-100', '/petugas/pengambilan'],
      ['Pengiriman tersedia', $kirimBaru, '📋', 'bg-blue-100', '/petugas/pengiriman'],
      ['Pengirimanku', $kirimSaya, '🚚', 'bg-indigo-100', '/petugas/pengiriman'],
      ['Diambil bulan ini', $ambilBulan, '📅', 'bg-green-100', '/petugas/riwayat'],
      ['Dikirim bulan ini', $kirimBulan, '✅', 'bg-purple-100', '/petugas/riwayat?tab=pengiriman'],
    ];
  @endphp

  <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach ($kartu as [$label, $angka, $ic, $warna, $url])
      <a href="{{ $url }}" class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl {{ $warna }} flex items-center justify-center text-xl">{{ $ic }}</div>
        <div class="text-2xl font-extrabold mt-3">{{ $angka }}</div>
        <div class="text-xs text-gray-500">{{ $label }}</div>
      </a>
    @endforeach
  </div>

  <div class="bg-white rounded-2xl shadow-sm mt-5 p-4 text-sm text-gray-600 space-y-1">
    <div class="font-extrabold text-gray-800">Alur kerja</div>
    <div>1. <b>Pengambilan</b>: ambil barang dari donatur, pindai label QR, lalu barang disimpan di posko.</div>
    <div>2. <b>Pengiriman</b>: setelah penerima mengajukan dan admin menyetujui, antar barang dari posko ke penerima.</div>
  </div>
@endsection