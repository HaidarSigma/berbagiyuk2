@extends('petugas.layout')
@section('judul', 'Riwayat')
@section('isi')
  <div class="flex gap-2 mb-4 text-sm">
    <a href="/petugas/riwayat?tab=pengambilan" class="px-4 py-2 rounded-full {{ $tab === 'pengambilan' ? 'bg-blue-700 text-white font-semibold' : 'bg-white text-gray-600' }}">Pengambilan</a>
    <a href="/petugas/riwayat?tab=pengiriman" class="px-4 py-2 rounded-full {{ $tab === 'pengiriman' ? 'bg-blue-700 text-white font-semibold' : 'bg-white text-gray-600' }}">Pengiriman</a>
  </div>

  @if ($tab === 'pengambilan')
    @php
      $stat = [
        'tersedia' => ['Di posko', 'bg-yellow-100 text-yellow-800'],
        'diproses' => ['Menunggu diantar', 'bg-blue-100 text-blue-800'],
        'selesai'  => ['Sudah terkirim', 'bg-green-100 text-green-800'],
      ];
    @endphp
    @forelse ($data as $tanggal => $daftar)
      <div class="mb-5">
        <div class="flex justify-between items-baseline mb-2">
          <h2 class="font-extrabold">{{ \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}</h2>
          <span class="text-xs text-gray-500">{{ $daftar->count() }} barang</span>
        </div>
        <div class="space-y-2">
          @foreach ($daftar as $p)
            @php $b = $p->barang; $s = $stat[$b->status] ?? ['-', 'bg-gray-100 text-gray-600']; @endphp
            <div class="bg-white rounded-xl shadow-sm p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
              <div>
                <div class="font-semibold">{{ $b->judul }}</div>
                <div class="text-xs text-gray-500">{{ $b->kategori->nama }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif · dari {{ $b->user->name }} ({{ $b->lokasi }}) · pukul {{ $p->diambil_at->format('H:i') }}</div>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $s[1] }}">{{ $s[0] }}</span>
                <a target="_blank" href="/bukti-pengambilan/{{ $p->id }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-700"><x-ikon nama="kamera" class="w-3.5 h-3.5" /> Bukti</a>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @empty
      <p class="text-gray-500">Belum ada barang yang diambil.</p>
    @endforelse
  @else
    <div class="space-y-3">
      @forelse ($data as $t)
        @php $b = $t->permintaan->barang; @endphp
        <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <div class="font-bold">{{ $b->judul }}</div>
            <div class="text-xs text-gray-500">{{ $b->kategori->nama }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif · untuk {{ $t->permintaan->penerima->name }} · selesai {{ $t->selesai_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
            @if ($t->catatan)<div class="text-xs text-gray-600 mt-1">Catatan: {{ $t->catatan }}</div>@endif
          </div>
          <a target="_blank" href="/bukti/{{ $t->id }}/serah" class="inline-flex items-center gap-1 text-sm font-semibold text-blue-700"><x-ikon nama="kamera" class="w-4 h-4" /> Bukti serah</a>
        </div>
      @empty
        <p class="text-gray-500">Belum ada pengantaran yang selesai.</p>
      @endforelse
    </div>
    <div class="mt-4">{{ $data->links() }}</div>
  @endif
@endsection