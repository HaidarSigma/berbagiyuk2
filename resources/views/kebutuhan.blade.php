@extends('layouts.situs')
@section('judul', 'Kebutuhan Mendesak - BerbagiYuk')
@section('isi')
  <div class="space-y-8">
    <div class="flex flex-col items-center gap-3 py-2 text-center">
      <div>
        <h1 class="text-2xl md:text-3xl font-extrabold inline-flex items-center gap-2"><x-ikon nama="paket" class="w-7 h-7 text-green-800" /> Kebutuhan Mendesak</h1>
        <p class="mx-auto mt-2 max-w-2xl text-sm md:text-base text-gray-600">Lihat situasi darurat, lokasi terdampak, dan barang yang dibutuhkan. Pilih kampanye agar donasimu disalurkan khusus untuk penerima korban bencana.</p>
      </div>
      @if (! auth()->check() || auth()->user()->role === 'donatur')
        <a href="/berbagi" class="bg-green-800 text-white px-5 py-2.5 rounded-xl font-semibold hover:bg-green-900 text-center">Bagikan Barang</a>
      @endif
    </div>

    <section>
      <h2 class="font-extrabold mb-3">Sedang dibutuhkan ({{ $terbuka->count() }})</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse ($terbuka as $k)
          <div id="kebutuhan-{{ $k->id }}" class="bg-white rounded-2xl shadow-sm overflow-hidden scroll-mt-24 {{ (int) request('fokus') === $k->id ? 'ring-2 ring-green-600' : '' }}">
            <div class="h-36 bg-green-100 text-green-700 flex items-center justify-center relative">
              @if ($k->foto)
                <img src="{{ asset('storage/'.$k->foto) }}" class="absolute inset-0 w-full h-full object-cover" alt="">
              @else
                <x-ikon nama="paket" class="w-12 h-12" />
              @endif
              <span class="absolute top-2 left-2 bg-orange-500 text-white text-[11px] font-semibold px-2 py-0.5 rounded-md">Mendesak</span>
            </div>
            <div class="space-y-3 p-5">
              <div class="font-bold leading-snug">{{ $k->judul }}</div>
              @if ($k->keterangan)<p class="text-sm leading-relaxed text-gray-600">{{ $k->keterangan }}</p>@endif
              <div class="flex items-center gap-1 text-xs leading-relaxed text-gray-500"><x-ikon nama="lokasi" class="w-3.5 h-3.5 text-gray-400" /> <span>{{ $k->lokasi }} · {{ $k->created_at->locale('id')->diffForHumans() }}</span></div>
              @if (! auth()->check() || auth()->user()->role === 'donatur')
                <a href="/berbagi?kebutuhan={{ $k->id }}" class="inline-flex w-full items-center justify-center rounded-lg bg-green-800 px-3 py-2 text-sm font-semibold text-white hover:bg-green-900">
                  Bagikan untuk kampanye ini
                </a>
              @endif
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
              <div class="font-semibold flex items-center gap-1.5"><x-ikon nama="cek" class="w-4 h-4 text-green-700" /> <span>{{ $k->judul }}</span></div>
              <div class="flex items-center gap-1 text-xs text-gray-500 mt-1"><x-ikon nama="lokasi" class="w-3.5 h-3.5" /> <span>{{ $k->lokasi }}</span></div>
            </div>
          @endforeach
        </div>
      </section>
    @endif
  </div>
@endsection