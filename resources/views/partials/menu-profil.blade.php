<div class="flex items-center gap-3 text-sm whitespace-nowrap">
  @include('partials.tombol-tema')
  @auth
    @if (auth()->user()->hasVerifiedEmail() && session('pin_ok'))
      @php $u = auth()->user(); @endphp
      <details class="relative" id="menuProfil">
        <summary class="list-none cursor-pointer flex items-center gap-2 select-none [&::-webkit-details-marker]:hidden">
          @if ($u->foto_profil)
  <img src="{{ asset('storage/'.$u->foto_profil) }}" class="w-8 h-8 rounded-full object-cover" alt="">
@else
  <span class="w-8 h-8 rounded-full bg-green-800 text-white flex items-center justify-center font-bold text-sm">{{ strtoupper(substr($u->name, 0, 1)) }}</span>
@endif
          <span class="hidden sm:block font-semibold">{{ \Illuminate\Support\Str::before($u->name, ' ') }}</span>
          <x-ikon nama="panah-bawah" class="w-3.5 h-3.5 text-gray-400" />
        </summary>

        <div class="absolute right-0 mt-2 w-60 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-40">
          <div class="px-4 py-2 border-b">
            <div class="font-semibold text-sm">{{ $u->name }}</div>
            <div class="text-xs text-gray-500">{{ $u->email }}</div>
            <span class="inline-block mt-1 text-[11px] bg-green-100 text-green-800 px-2 py-0.5 rounded-full">{{ ucfirst($u->role) }}</span>
          </div>

          @include('partials.notifikasi-link', ['kelas' => 'flex items-center gap-2 px-4 py-2 hover:bg-gray-50'])

          @if ($u->role === 'admin')
            <a href="/admin" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"><x-ikon nama="alat" class="w-4 h-4 text-gray-500" /> <span>Panel Admin</span></a>
          @endif
          @if ($u->role === 'petugas')
          <a href="/petugas" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"><x-ikon nama="truk" class="w-4 h-4 text-gray-500" /> <span>Panel Petugas</span></a>
          @endif
          @if ($u->role === 'donatur')
            <a href="/barang-saya" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"><x-ikon nama="paket" class="w-4 h-4 text-gray-500" /> <span>Barang Saya</span></a>
          @endif
          @if ($u->role === 'penerima')
            <a href="/permintaan-saya" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"><x-ikon nama="surat" class="w-4 h-4 text-gray-500" /> <span>Permintaan Saya</span></a>
            <a href="/status-penerima" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"><x-ikon nama="centang-lingkaran" class="w-4 h-4 text-gray-500" /> <span>Status Verifikasi</span></a>
          @endif

          <a href="/pengaturan" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-50"><x-ikon nama="pengaturan" class="w-4 h-4 text-gray-500" /> <span>Pengaturan</span></a>
          <form method="POST" action="/keluar" class="border-t mt-1 pt-1">@csrf
            <button class="w-full flex items-center gap-2 text-left px-4 py-2 text-red-600 hover:bg-red-50"><x-ikon nama="keluar" class="w-4 h-4" /> <span>Keluar</span></button>
          </form>
        </div>
      </details>
      <script nonce="{{ $cspNonce ?? '' }}">
        document.addEventListener('click', e => {
          const d = document.getElementById('menuProfil');
          if (d && !d.contains(e.target)) d.removeAttribute('open');
        });
      </script>
    @else
      <a href="/pin/masuk" class="bg-yellow-100 text-yellow-800 px-3 py-1 rounded-lg font-semibold">Selesaikan verifikasi</a>
      <form method="POST" action="/keluar">@csrf<button class="text-gray-500">Keluar</button></form>
    @endif
  @else
    <a href="/masuk" class="font-semibold">Masuk</a>
    <a href="/daftar" class="bg-green-800 text-white px-4 py-2 rounded-lg hover:bg-green-900 transition">Daftar</a>
  @endauth
</div>