<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul') - Petugas BerbagiYuk</title>
  @include('partials.head-asset')
  <style>
    body.petugas-panel {
      background-color: #dce7f2;
      background-image:
        radial-gradient(circle at 82% 8%, rgba(59, 130, 246, .28), transparent 24rem),
        radial-gradient(circle at 18% 92%, rgba(14, 165, 233, .22), transparent 22rem),
        linear-gradient(135deg, #eef4fa 0%, #dce7f2 55%, #cbdbea 100%);
      background-attachment: fixed;
    }
  </style>
</head>
<body class="petugas-panel font-sans text-gray-800">

@php
  $menu = [['/petugas', '🏠', 'Beranda'], ['/petugas/pengambilan', '📦', 'Pengambilan'], ['/petugas/pengiriman', '🚚', 'Pengiriman'], ['/petugas/riwayat', '🗂️', 'Riwayat']];
  $item = 'flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm text-left';
@endphp

<div class="flex min-h-screen">

  <aside class="hidden md:flex sticky top-0 h-screen w-60 shrink-0 flex-col bg-blue-900 text-blue-100 p-4">
    <div class="px-3 py-3 mb-4">
      <div class="text-xl font-extrabold text-white leading-tight">BerbagiYuk</div>
      <div class="text-xs text-blue-300">Panel Petugas</div>
    </div>

    <nav class="space-y-1">
      @foreach ($menu as [$url, $ic, $nama])
        <a href="{{ $url }}" class="{{ $item }} {{ request()->is(trim($url, '/')) ? 'bg-blue-700 text-white font-semibold' : 'hover:bg-blue-800' }}">
          <span class="w-5 text-center">{{ $ic }}</span><span>{{ $nama }}</span>
        </a>
      @endforeach
    </nav>

    <div class="mt-auto space-y-1 border-t border-blue-800 pt-3">
      <a href="/?lihat=1" class="{{ $item }} hover:bg-blue-800"><span class="w-5 text-center">🌐</span><span>Lihat Situs</span></a>
      <a href="/pengaturan" class="{{ $item }} hover:bg-blue-800"><span class="w-5 text-center">⚙️</span><span>Pengaturan</span></a>
      <form method="POST" action="/keluar">@csrf
        <button class="{{ $item }} hover:bg-red-600 hover:text-white"><span class="w-5 text-center">🚪</span><span>Keluar</span></button>
      </form>
    </div>
  </aside>

  <div class="flex-1 min-w-0 pb-20 md:pb-0">
    <header class="sticky top-0 z-10 bg-white border-b px-4 md:px-6 h-14 flex justify-between items-center">
      <h1 class="font-extrabold text-lg">@yield('judul')</h1>
      <div class="flex items-center gap-3 text-sm">
        @if (auth()->user()->foto_profil)
          <img src="{{ asset('storage/'.auth()->user()->foto_profil) }}" class="w-8 h-8 rounded-full object-cover" alt="">
        @else
          <div class="w-8 h-8 rounded-full bg-blue-700 text-white flex items-center justify-center font-bold">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
        @endif
        <span class="hidden sm:block font-semibold">{{ \Illuminate\Support\Str::before(auth()->user()->name, ' ') }}</span>
      </div>
    </header>

    <main class="relative p-4 md:p-6">
      @if (session('status'))
        <div class="mb-4 bg-green-50 text-green-800 text-sm rounded-lg p-3">{{ session('status') }}</div>
      @endif
      @if ($errors->any())
        <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
      @endif
      @yield('isi')
    </main>
  </div>
</div>

<nav class="md:hidden fixed bottom-0 inset-x-0 z-30 bg-white border-t grid grid-cols-4 text-center text-[11px]">
  @foreach ($menu as [$url, $ic, $nama])
    <a href="{{ $url }}" class="py-2 {{ request()->is(trim($url, '/')) ? 'text-blue-700 font-bold' : 'text-gray-500' }}">
      <div class="text-xl leading-none">{{ $ic }}</div>{{ $nama }}
    </a>
  @endforeach
</nav>
@include('partials.toast')
@include('partials.interaksi')
</body>
</html>