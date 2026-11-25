@extends('layouts.situs')
@section('judul', 'Berita - BerbagiYuk')
@section('isi')
  <div class="space-y-6">
    <div>
      <h1 class="text-3xl font-extrabold">📰 Berita Terbaru</h1>
      <p class="text-gray-600 mt-1">Informasi dan kabar terbaru dari BerbagiYuk.</p>
    </div>

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      @forelse ($daftar as $b)
        <a href="/berita/{{ $b->id }}" class="bg-white rounded-2xl shadow-sm overflow-hidden hover:shadow-md transition">
          <div class="h-44 bg-green-100 flex items-center justify-center text-6xl overflow-hidden">
            @if ($b->foto)
              <img src="{{ asset('storage/'.$b->foto) }}" class="w-full h-full object-cover" alt="{{ $b->judul }}">
            @else
              📰
            @endif
          </div>
          <div class="p-5 space-y-2">
            <div class="text-xs text-gray-500">{{ $b->tanggal->locale('id')->translatedFormat('d F Y') }}</div>
            <h2 class="text-lg font-extrabold leading-snug line-clamp-2">{{ $b->judul }}</h2>
            <p class="text-sm text-gray-600 line-clamp-3">{{ $b->ringkasan }}</p>
            <span class="inline-block pt-1 text-sm font-semibold text-green-800">Baca selengkapnya →</span>
          </div>
        </a>
      @empty
        <p class="text-gray-500 col-span-full">Belum ada berita yang diterbitkan.</p>
      @endforelse
    </div>

    @if ($daftar->hasPages())
      <div>{{ $daftar->links() }}</div>
    @endif
  </div>
@endsection
