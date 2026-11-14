@extends('petugas.layout')
@section('judul', 'Riwayat')
@section('isi')
  <div class="flex gap-2 mb-4 text-sm">
    <a href="/petugas/riwayat?tab=pengambilan" class="px-4 py-2 rounded-full {{ $tab === 'pengambilan' ? 'bg-blue-700 text-white font-semibold' : 'bg-white text-gray-600' }}">Pengambilan</a>
    <a href="/petugas/riwayat?tab=pengiriman" class="px-4 py-2 rounded-full {{ $tab === 'pengiriman' ? 'bg-blue-700 text-white font-semibold' : 'bg-white text-gray-600' }}">Pengiriman</a>
  </div>

  @if ($tab === 'pengambilan')
    @forelse ($tugas as $tanggal => $daftar)
      <div class="mb-5">
        <div class="flex justify-between items-baseline mb-2">
          <h2 class="font-extrabold">{{ \Illuminate\Support\Carbon::parse($tanggal)->locale('id')->translatedFormat('l, d F Y') }}</h2>
          <span class="text-xs text-gray-500">{{ $daftar->count() }} barang</span>
        </div>
        <div class="space-y-2">
          @foreach ($daftar as $t)
            @php $b = $t->permintaan->barang; @endphp
            <div class="bg-white rounded-xl shadow-sm p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-sm">
              <div>
                <div class="font-semibold">{{ $b->judul }}</div>
                <div class="text-xs text-gray-500">{{ $b->kategori->nama }} · dari {{ $b->user->name }} ({{ $b->lokasi }}) · pukul {{ $t->diambil_at->format('H:i') }}</div>
              </div>
              <div class="flex items-center gap-3">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $t->status === 'selesai' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800' }}">{{ $t->status === 'selesai' ? 'Sudah terkirim' : 'Menunggu diantar' }}</span>
                <a target="_blank" href="/bukti/{{ $t->id }}/ambil" class="text-xs font-semibold text-blue-700">📷 Bukti</a>
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
      @forelse ($tugas as $t)
        @php $b = $t->permintaan->barang; @endphp
        <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <div class="font-bold">{{ $b->judul }}</div>
            <div class="text-xs text-gray-500">{{ $b->kategori->nama }} · untuk {{ $t->permintaan->penerima->name }} · selesai {{ $t->selesai_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
            @if ($t->catatan)<div class="text-xs text-gray-600 mt-1">Catatan: {{ $t->catatan }}</div>@endif
          </div>
          <div class="flex gap-4 text-sm font-semibold text-blue-700">
            <a target="_blank" href="/bukti/{{ $t->id }}/ambil">📷 Bukti ambil</a>
            <a target="_blank" href="/bukti/{{ $t->id }}/serah">📷 Bukti serah</a>
          </div>
        </div>
      @empty
        <p class="text-gray-500">Belum ada pengantaran yang selesai.</p>
      @endforelse
    </div>
    <div class="mt-4">{{ $tugas->links() }}</div>
  @endif
@endsection