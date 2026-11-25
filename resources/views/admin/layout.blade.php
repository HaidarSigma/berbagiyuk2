<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('judul') - Admin BerbagiYuk</title>
  @include('partials.head-asset')
  <style>
    body.admin-panel {
      background-color: #dceadf;
      background-image:
        radial-gradient(circle at 82% 8%, rgba(34, 197, 94, .28), transparent 24rem),
        radial-gradient(circle at 18% 92%, rgba(20, 184, 166, .22), transparent 22rem),
        linear-gradient(135deg, #eef7f0 0%, #dceadf 55%, #cedfd4 100%);
      background-attachment: fixed;
    }
  </style>
</head>
<body class="admin-panel font-sans text-gray-800">

@php
  $item = 'flex items-center gap-3 w-full px-3 py-2 rounded-lg text-sm text-left';
  $menu = [['/admin', '📊', 'Dashboard'], ['/admin/barang', '✅', 'Verifikasi Barang'], ['/admin/penerima', '👥', 'Verifikasi Penerima'], ['/admin/permintaan', '📨', 'Permintaan Masuk'],['/admin/log', '🧾', 'Log Aktivitas'],['/admin/pengguna', '👤', 'Kelola Pengguna'],['/admin/kebutuhan', '🚨', 'Kebutuhan Mendesak'],['/admin/pengiriman', '🚚', 'Riwayat Pengiriman'],['/admin/berita', '📰', 'Berita'],['/admin/peringatan', '🚨', 'Peringatan Keamanan']];
@endphp

<div class="flex min-h-screen">

  {{-- Sidebar --}}
  <aside class="hidden md:flex sticky top-0 h-screen w-60 shrink-0 flex-col bg-green-900 text-green-100 p-4">
    <div class="px-3 py-3 mb-4">
      <div class="text-xl font-extrabold text-white leading-tight">BerbagiYuk</div>
      <div class="text-xs text-green-300">Panel Admin</div>
    </div>

    <nav class="space-y-1">
      @foreach ($menu as [$url, $ic, $nama])
        <a href="{{ $url }}" class="{{ $item }} {{ request()->is(trim($url, '/')) ? 'bg-green-700 text-white font-semibold' : 'hover:bg-green-800' }}">
          <span class="w-5 text-center">{{ $ic }}</span><span>{{ $nama }}</span>
@if (($badge[$url] ?? 0) > 0)
  <span class="ml-auto text-[11px] bg-red-500 text-white rounded-full px-2 py-0.5">{{ $badge[$url] }}</span>
@endif
        </a>
      @endforeach
    </nav>

    <div class="mt-4 space-y-1 border-t border-green-800 pt-3">
      <a href="/?lihat=1" class="{{ $item }} hover:bg-green-800">
        <span class="w-5 text-center">🌐</span><span>Lihat Situs</span>
      </a>
      <form method="POST" action="/keluar">@csrf
        <button class="{{ $item }} hover:bg-red-600 hover:text-white">
          <span class="w-5 text-center">🚪</span><span>Keluar</span>
        </button>
      </form>
    </div>
  </aside>

  {{-- Konten --}}
  <div class="flex-1 min-w-0">
    <header class="sticky top-0 z-10 bg-white border-b px-6 h-14 flex justify-between items-center">
      <h1 class="font-extrabold text-lg">@yield('judul')</h1>
      <div class="flex items-center gap-3 text-sm">
        <span class="md:hidden flex gap-3 text-green-800 font-semibold">
          <a href="/admin">Dashboard</a><a href="/admin/barang">Verifikasi</a>
        </span>
        @if (auth()->user()->foto_profil)
          <img src="{{ asset('storage/'.auth()->user()->foto_profil) }}" class="w-8 h-8 rounded-full object-cover" alt="Foto profil">
        @else
          <div class="w-8 h-8 rounded-full bg-green-800 text-white flex items-center justify-center font-bold">
            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
          </div>
        @endif
        <span class="hidden sm:block font-semibold">{{ auth()->user()->name }}</span>
      </div>
    </header>

    <main class="relative p-6">
      @if (session('status'))
        <div class="mb-4 bg-green-50 text-green-800 text-sm rounded-lg p-3">{{ session('status') }}</div>
      @endif
      @yield('isi')
      @if (($peringatanBaru ?? 0) > 0 && ! request()->is('admin/peringatan'))
        <a href="/admin/peringatan" class="mb-4 flex items-center gap-2 bg-red-600 text-white text-sm rounded-lg px-4 py-2 font-semibold hover:bg-red-700">
    ⚠️ {{ $peringatanBaru }} peringatan keamanan baru. Periksa sekarang →
    </a>
  @endif
    </main>
  </div>
</div>
@include('partials.toast')
@include('partials.interaksi')
</body>
</html>