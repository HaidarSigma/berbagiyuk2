<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul', 'BerbagiYuk')</title>
  @include('partials.head-asset')
</head>
<body class="site-publik flex min-h-screen flex-col bg-gray-50 text-gray-800 font-sans">

@php
  $aktif = 'py-1 border-b-2 border-green-800 text-green-800 font-semibold';
  $biasa = 'py-1 border-b-2 border-transparent hover:text-green-800';
  $urlBeranda = auth()->check() && in_array(auth()->user()->role, ['admin', 'petugas'], true) ? '/?lihat=1' : '/';
@endphp

<nav class="bg-white/90 backdrop-blur sticky top-0 z-30 border-b border-gray-100">
  <div class="max-w-6xl mx-auto flex items-center justify-between gap-4 px-4 py-3">
    <a href="{{ $urlBeranda }}">
      <span>
        <span class="block text-xl font-extrabold text-green-800 leading-none">BerbagiYuk</span>
        <span class="block text-[11px] text-gray-500">Berbagi Sedikit, Berarti Banyak.</span>
      </span>
    </a>

    <div class="hidden lg:flex items-center gap-6 text-sm font-medium whitespace-nowrap">
      <a href="{{ $urlBeranda }}" class="{{ $biasa }}">Beranda</a>
      <a href="/cari" class="{{ request()->is('cari*', 'barang/*') ? $aktif : $biasa }}">Cari Barang</a>
      @if (! auth()->check() || auth()->user()->role === 'donatur')
        <a href="/berbagi" class="{{ $biasa }}">Berbagi Barang</a>
      @endif
      <a href="/berita" class="{{ request()->is('berita*') ? $aktif : $biasa }}">Berita</a>
      <a href="/tentang" class="{{ request()->is('tentang') ? $aktif : $biasa }}">Tentang Kami</a>
    </div>

    @unless (request()->is('cari*', 'berita*', 'tentang'))
      <form action="/cari" method="GET" class="hidden md:flex items-center bg-gray-100 rounded-full px-4 py-2 w-56 text-sm">
        <x-ikon nama="cari" class="w-4 h-4 text-gray-400 mr-2" />
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari barang, kategori..." data-placeholder-asli="Cari barang, kategori..." data-placeholder-animasi="Cari pakaian...|Cari buku...|Cari perlengkapan rumah..." class="bg-transparent outline-none focus-visible:outline-none w-full">
      </form>
    @endunless

    @include('partials.menu-profil')
  </div>
</nav>

<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-6">
  @yield('isi')
</main>

@include('partials.footer')
@include('partials.toast')
@include('partials.sukses')
@include('partials.interaksi')
@include('partials.notifikasi-toast')
</body>
</html>