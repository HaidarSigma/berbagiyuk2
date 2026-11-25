@extends('layouts.situs')
@section('judul', $berita->judul.' - BerbagiYuk')
@section('isi')
  <article class="max-w-3xl mx-auto space-y-4">
    <a href="/berita" class="text-sm text-green-800 font-semibold">← Semua berita</a>
    <div class="text-xs text-gray-500">{{ $berita->tanggal->locale('id')->translatedFormat('l, d F Y') }}</div>
    <h1 class="text-3xl font-extrabold leading-tight">{{ $berita->judul }}</h1>
    <p class="text-lg text-gray-600">{{ $berita->ringkasan }}</p>

    @if ($berita->foto)
      <img src="{{ asset('storage/'.$berita->foto) }}" class="mx-auto max-h-[280px] md:max-h-[360px] max-w-full object-contain rounded-2xl" alt="{{ $berita->judul }}">
    @endif

    @if ($berita->isi)
      <div class="text-gray-800 leading-relaxed whitespace-pre-line">{!! e($berita->isi) !!}</div>
    @endif
  </article>

  @if ($lain->count())
    <section class="max-w-3xl mx-auto mt-10">
      <h2 class="font-extrabold mb-3">Berita lainnya</h2>
      <div class="grid sm:grid-cols-3 gap-3">
        @foreach ($lain as $b)
          <a href="/berita/{{ $b->id }}" class="bg-white rounded-xl shadow-sm p-3 text-sm hover:shadow-md transition">
            <div class="text-xs text-gray-500">{{ $b->tanggal->locale('id')->translatedFormat('d M Y') }}</div>
            <div class="font-semibold leading-snug">{{ $b->judul }}</div>
          </a>
        @endforeach
      </div>
    </section>
  @endif
@endsection
