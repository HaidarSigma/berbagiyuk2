@extends('petugas.layout')
@section('judul', 'Beranda Petugas')
@section('isi')
  @php
    $kartu = [
      ['Pengambilan tersedia', $ambilBaru, 'paket', 'bg-yellow-100 text-yellow-700', '/petugas/pengambilan'],
      ['Pengambilanku', $ambilSaya, 'keranjang', 'bg-orange-100 text-orange-700', '/petugas/pengambilan'],
      ['Pengiriman tersedia', $kirimBaru, 'daftar', 'bg-blue-100 text-blue-700', '/petugas/pengiriman'],
      ['Pengirimanku', $kirimSaya, 'truk', 'bg-indigo-100 text-indigo-700', '/petugas/pengiriman'],
      ['Diambil bulan ini', $ambilBulan, 'kalender', 'bg-green-100 text-green-700', '/petugas/riwayat'],
      ['Dikirim bulan ini', $kirimBulan, 'centang-lingkaran', 'bg-purple-100 text-purple-700', '/petugas/riwayat?tab=pengiriman'],
    ];
  @endphp

  <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach ($kartu as [$label, $angka, $ic, $warna, $url])
      <a href="{{ $url }}" class="bg-white rounded-2xl p-4 shadow-sm hover:shadow-md transition">
        <div class="w-10 h-10 rounded-xl {{ $warna }} flex items-center justify-center"><x-ikon :nama="$ic" class="w-5 h-5" /></div>
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