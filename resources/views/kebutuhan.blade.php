@extends('layouts.situs')
@section('judul', 'Kebutuhan Mendesak - BerbagiYuk')
@section('isi')
  <div class="space-y-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-3">
      <div>
        <h1 class="text-3xl font-extrabold">📦 Kebutuhan Mendesak</h1>
        <p class="text-gray-600 mt-1 max-w-2xl">Barang yang saat ini sedang sangat dibutuhkan. Punya barang yang sesuai? Bagikan lewat BerbagiYuk.</p>
      </div>
      @if (! auth()->check() || auth()->user()->role === 'donatur')
        <a href="/berbagi" class="bg-green-800 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-green-900 text-center">Bagikan Barang</a>
      @endif
    </div>

    <section>
      <h2 class="font-extrabold mb-3">Sedang dibutuhkan ({{ $terbuka->count() }})</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($terbuka as $k)
          <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
            <div class="h-36 bg-green-100 flex items-center justify-center relative">
              @if ($k->foto)
                <img src="{{ asset('storage/'.$k->foto) }}" class="absolute inset-0 w-full h-full object-cover" alt="">
              @else
                <span class="text-5xl">📦</span>
              @endif
              <span class="absolute top-2 left-2 bg-orange-500 text-white text-[11px] font-semibold px-2 py-0.5 rounded-md">Mendesak</span>
            </div>
            <div class="p-4 space-y-1">
              <div class="font-bold">{{ $k->judul }}</div>
              <p class="text-sm text-gray-600">{{ $k->keterangan }}</p>
              <div class="text-xs text-gray-500 pt-1">📍 {{ $k->lokasi }} · {{ $k->created_at->locale('id')->diffForHumans() }}</div>
            </div>
          </div>
        @empty
          <p class="text-gray-500 col-span-full">Belum ada kebutuhan mendesak saat ini.</p>
        @endforelse
      </div>
    </section>

    @if ($selesai->count())
      <section>
        <h2 class="font-extrabold mb-3 text-gray-500">Sudah terpenuhi</h2>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          @foreach ($selesai as $k)
            <div class="bg-white/70 rounded-xl p-4 text-sm opacity-70">
              <div class="font-semibold">✔ {{ $k->judul }}</div>
              <div class="text-xs text-gray-500">📍 {{ $k->lokasi }}</div>
            </div>
          @endforeach
        </div>
      </section>
    @endif
  </div>
@endsection