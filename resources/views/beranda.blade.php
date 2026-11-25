<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>BerbagiYuk</title>
  @include('partials.head-asset')
</head>
<body class="site-publik bg-gray-50 text-gray-800 font-sans">

@php
  $ikon  = ['👕','📚','🪑','🏠','🎒','💻','🍽️','⋯'];
  $warna = ['bg-green-100','bg-blue-100','bg-orange-100','bg-purple-100','bg-rose-100','bg-cyan-100','bg-red-100','bg-gray-100'];
  $adaFoto = file_exists(public_path('img/hero.jpg'));
  $urlBeranda = auth()->check() && in_array(auth()->user()->role, ['admin', 'petugas'], true) ? '/?lihat=1' : '/';
@endphp

{{-- Navbar --}}
<nav class="bg-white/90 backdrop-blur sticky top-0 z-30 border-b border-gray-100">
  <div class="max-w-6xl mx-auto flex items-center justify-between gap-4 px-4 py-3">
    <a href="{{ $urlBeranda }}">
      <span>
        <span class="block text-xl font-extrabold text-green-800 leading-none">BerbagiYuk</span>
        <span class="block text-[11px] text-gray-500">Berbagi Sedikit, Berarti Banyak.</span>
      </span>
    </a>

      <div class="hidden lg:flex items-center gap-6 text-sm font-medium whitespace-nowrap">
      <a href="{{ $urlBeranda }}" class="py-1 border-b-2 border-green-800 text-green-800 font-semibold">Beranda</a>
      <a href="/cari" class="py-1 border-b-2 border-transparent hover:text-green-800">Cari Barang</a>
      @if (! auth()->check() || auth()->user()->role === 'donatur')
      <a href="/berbagi" class="py-1 border-b-2 border-transparent hover:text-green-800">Berbagi Barang</a>
      @endif
      <a href="/berita" class="py-1 border-b-2 border-transparent hover:text-green-800">Berita</a>
      <a href="/tentang" class="py-1 border-b-2 border-transparent hover:text-green-800">Tentang Kami</a>
    </div>

    <form action="/cari" method="GET" class="hidden md:flex items-center bg-gray-100 rounded-full px-4 py-2 w-56 text-sm">
      <span class="text-gray-400 mr-2">🔍</span>
      <input type="text" name="q" placeholder="Cari barang, kategori..." data-placeholder-asli="Cari barang, kategori..." data-placeholder-animasi="Cari pakaian...|Cari buku...|Cari perlengkapan rumah..." class="bg-transparent outline-none focus-visible:outline-none w-full">
    </form>

    @include('partials.menu-profil')
    
  </div>
</nav>

<main class="max-w-6xl mx-auto px-4 py-6 space-y-8">

{{-- Hero + Berita --}}
@php
  $dataBerita = $berita->map(fn ($b) => [
      'id'        => $b->id,
      'judul'     => $b->judul,
      'tanggal'   => $b->tanggal->locale('id')->translatedFormat('l, d F Y'),
      'ringkasan' => $b->ringkasan,
      'isi'       => $b->isi,
      'foto'      => $b->foto ? asset('storage/'.$b->foto) : null,
  ])->values();

  // Slide: foto hero dulu, lalu gambar berita (idx = urutan berita untuk popup)
  $slides = collect();
  if ($hero) { $slides->push(['idx' => null, 'foto' => $hero]); }
  foreach ($berita as $k => $b) {
      if ($b->foto) { $slides->push(['idx' => $k, 'foto' => asset('storage/'.$b->foto), 'judul' => $b->judul]); }
  }
@endphp

<section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 via-sky-50 to-amber-50">

  @if ($slides->isNotEmpty())
    <div class="absolute inset-y-0 right-0 w-full md:w-[58%]">
      {{-- Gambar: memudar ke kiri supaya teks tetap terbaca --}}
      <div class="absolute inset-0 opacity-30 md:opacity-100"
           style="-webkit-mask-image: linear-gradient(to right, transparent 0%, #000 38%); mask-image: linear-gradient(to right, transparent 0%, #000 38%);">
        @foreach ($slides as $n => $sl)
          <div class="hero-slide absolute inset-0 transition-opacity duration-700 {{ $n === 0 ? 'opacity-100' : 'opacity-0 pointer-events-none' }} {{ $sl['idx'] !== null ? 'cursor-pointer' : '' }}"
               @if ($sl['idx'] !== null) data-berita="{{ $sl['idx'] }}" role="button" tabindex="0" aria-label="Baca berita: {{ $sl['judul'] }}" @endif>
            <img src="{{ $sl['foto'] }}" alt="" @if ($n > 0) loading="lazy" @endif
                 class="w-full h-full object-cover" style="object-position: 50% 30%;">
          </div>
        @endforeach
      </div>

      {{-- Chip judul berita (hanya tampil di slide berita) --}}
      <button id="hero-chip" type="button" class="hidden absolute top-4 right-4 z-20 max-w-[85%] bg-white/90 hover:bg-white shadow rounded-full px-4 py-2 text-xs font-semibold text-green-800 truncate">
        📰 <span id="hero-chip-teks"></span> →
      </button>

      @if ($slides->count() > 1)
        <div class="absolute bottom-4 right-4 z-20 flex items-center gap-2 bg-white/80 backdrop-blur rounded-full px-2 py-1 shadow">
          <button id="hero-prev" type="button" aria-label="Sebelumnya" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full hover:bg-white">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="block h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><path d="m15 18-6-6 6-6"/></svg>
          </button>
          <div id="hero-dots" class="flex h-6 items-center gap-1.5">
            @foreach ($slides as $n => $sl)
              <button type="button" aria-label="Gambar {{ $n + 1 }}" class="block h-2 w-2 shrink-0 rounded-full {{ $n === 0 ? 'bg-green-700' : 'bg-gray-300' }}"></button>
            @endforeach
          </div>
          <button id="hero-next" type="button" aria-label="Berikutnya" class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full hover:bg-white">
            <svg aria-hidden="true" viewBox="0 0 24 24" class="block h-3 w-3" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
          </button>
        </div>
      @endif
    </div>
  @else
    {{-- Hiasan bawaan bila tidak ada gambar sama sekali --}}
    <div class="absolute -top-10 -right-16 w-72 h-72 rounded-full bg-green-200/60"></div>
    <div class="absolute -bottom-24 right-32 w-64 h-64 rounded-full bg-green-200/60"></div>
    <div class="hidden md:flex absolute top-10 right-10 items-start gap-3">
      <div class="w-28 h-28 bg-white rounded-2xl shadow-lg flex items-center justify-center text-5xl -rotate-6">👕</div>
      <div class="w-28 h-28 bg-white rounded-2xl shadow-lg flex items-center justify-center text-5xl mt-5">📦</div>
      <div class="w-28 h-28 bg-white rounded-2xl shadow-lg flex items-center justify-center text-5xl rotate-6">📚</div>
    </div>
    <div class="hidden md:block absolute bottom-10 right-12 text-right text-2xl text-green-800 -rotate-6" style="font-family: 'Segoe Script', 'Comic Sans MS', cursive;">
      Sedikit berbagi,<br>banyak kebahagiaan ♥
    </div>
  @endif

  {{-- Teks hero: tetap diam --}}
  <div class="relative z-10 px-6 py-12 md:px-14 md:py-20 md:max-w-[52%] space-y-5">
    <span class="inline-block bg-white text-green-800 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">🌱 Berbagi barang, tanpa biaya</span>
    <h1 class="text-4xl md:text-5xl font-extrabold leading-tight text-gray-900">Barang tak terpakai, <span class="text-green-700">manfaat bagi sesama.</span></h1>
    <p class="text-gray-600 max-w-md">Temukan barang gratis atau bagikan barang layak pakai kepada orang yang membutuhkan.</p>

    <div class="flex flex-wrap gap-3 pt-1">
      @if (! auth()->check() || auth()->user()->role === 'donatur')
        <a href="/berbagi" class="bg-green-800 text-white px-6 py-3 rounded-xl font-semibold shadow hover:bg-green-900 transition">+ Bagikan Barang</a>
      @endif
      <a href="/cari" class="bg-white/80 border-2 border-green-800 text-green-800 px-6 py-3 rounded-xl font-semibold hover:bg-white transition">🔍 Cari Barang</a>
    </div>

    <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-gray-600">
      <span>✔ Barang diperiksa admin</span>
      <span>✔ Penjemputan dibantu petugas</span>
      <span>✔ 100% gratis</span>
    </div>
  </div>
</section>

@if ($slides->isNotEmpty())
  {{-- Jendela berita --}}
  <div id="modal-berita" class="hidden fixed inset-0 z-50 bg-black/60 items-center justify-center p-4" role="dialog" aria-modal="true">
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
      <button type="button" id="mb-tutup" aria-label="Tutup" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 shadow text-gray-600 z-10">✕</button>
      <img id="mb-foto" class="hidden w-full max-h-72 object-cover rounded-t-2xl" alt="">
      <div class="p-6 space-y-3">
        <div id="mb-tanggal" class="text-xs text-gray-500"></div>
        <h3 id="mb-judul" class="text-2xl font-extrabold leading-snug"></h3>
        <p id="mb-ringkasan" class="text-gray-600"></p>
        <div id="mb-isi" class="text-gray-800 leading-relaxed whitespace-pre-line"></div>
        <a id="mb-link" href="#" class="inline-block text-sm font-semibold text-green-800">Buka halaman berita →</a>
      </div>
    </div>
  </div>

  <script nonce="{{ $cspNonce ?? '' }}">
    (() => {
      const data  = @json($dataBerita);
      const s     = [...document.querySelectorAll('.hero-slide')];
      const d     = [...document.querySelectorAll('#hero-dots button')];
      const chip  = document.getElementById('hero-chip');
      const modal = document.getElementById('modal-berita');
      let i = 0, t, chipIdx = null;

      const tampil = n => {
        i = (n + s.length) % s.length;
        s.forEach((x, k) => {
          x.classList.toggle('opacity-100', k === i);
          x.classList.toggle('opacity-0', k !== i);
          x.classList.toggle('pointer-events-none', k !== i);
        });
        d.forEach((x, k) => { x.classList.toggle('bg-green-700', k === i); x.classList.toggle('bg-gray-300', k !== i); });

        const idx = s[i].dataset.berita;
        chipIdx = idx === undefined ? null : +idx;
        if (chipIdx === null) {
          chip.classList.add('hidden');
        } else {
          document.getElementById('hero-chip-teks').textContent = data[chipIdx].judul;
          chip.classList.remove('hidden');
        }
      };
      const mulai = () => { clearInterval(t); if (s.length > 1) t = setInterval(() => tampil(i + 1), 6000); };

      const buka = k => {
        const b = data[k];
        document.getElementById('mb-tanggal').textContent   = b.tanggal;
        document.getElementById('mb-judul').textContent     = b.judul;
        document.getElementById('mb-ringkasan').textContent = b.ringkasan;
        document.getElementById('mb-isi').textContent       = b.isi || '';
        document.getElementById('mb-link').href             = '/berita/' + b.id;

        const f = document.getElementById('mb-foto');
        if (b.foto) { f.src = b.foto; f.alt = b.judul; f.classList.remove('hidden'); } else { f.classList.add('hidden'); }

        modal.classList.remove('hidden'); modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        clearInterval(t);   // slider berhenti selama berita dibaca
      };
      const tutup = () => {
        modal.classList.add('hidden'); modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
        mulai();
      };

      s.forEach(x => {
        if (x.dataset.berita === undefined) return;
        x.onclick = () => buka(+x.dataset.berita);
        x.onkeydown = e => { if (e.key === 'Enter') buka(+x.dataset.berita); };
      });
      chip.onclick = () => { if (chipIdx !== null) buka(chipIdx); };

      document.getElementById('mb-tutup').onclick = tutup;
      modal.onclick = e => { if (e.target === modal) tutup(); };
      document.addEventListener('keydown', e => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) tutup(); });

      if (s.length > 1) {
        document.getElementById('hero-prev').onclick = () => { tampil(i - 1); mulai(); };
        document.getElementById('hero-next').onclick = () => { tampil(i + 1); mulai(); };
        d.forEach((x, k) => x.onclick = () => { tampil(k); mulai(); });
      }
      tampil(0);
      mulai();
    })();
  </script>
@endif

  {{-- Kategori --}}
  <section class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
    @foreach ($kategori as $i => $k)
      <a href="/cari?kategori={{ $k->id }}" class="group bg-white rounded-2xl p-3 text-center shadow-sm hover:shadow-md hover:-translate-y-0.5 transition">
        <div class="w-12 h-12 mx-auto rounded-full {{ $warna[$i] ?? 'bg-gray-100' }} flex items-center justify-center text-2xl group-hover:scale-110 transition">{{ $ikon[$i] ?? '📦' }}</div>
        <div class="text-xs font-semibold mt-2 leading-tight">{{ $k->nama }}</div>
      </a>
    @endforeach
  </section>

  <div class="grid lg:grid-cols-3 gap-6">

    {{-- Barang Gratis Terbaru --}}
    <section class="lg:col-span-2">
      <div class="flex justify-between items-center mb-3">
        <h2 class="text-xl font-extrabold">📢 Barang Gratis Terbaru</h2>
        <a href="/cari" class="text-sm text-green-800 font-semibold">Lihat Semua →</a>
      </div>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @forelse ($barang as $b)
          <a href="/barang/{{ $b->id }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition overflow-hidden">
            <div class="h-32 relative flex items-center justify-center {{ $warna[$b->kategori_id - 1] ?? 'bg-gray-100' }}">
              @if ($b->foto)
                <img src="{{ asset('storage/'.$b->foto) }}" class="absolute inset-0 w-full h-full object-cover">
              @else
                <span class="text-5xl group-hover:scale-110 transition">{{ $ikon[$b->kategori_id - 1] ?? '📦' }}</span>
              @endif
              <span class="absolute bottom-2 left-2 bg-green-700 text-white text-[11px] font-semibold px-2 py-0.5 rounded-md">Tersedia</span>
            </div>
            <div class="p-3">
              <div class="text-sm font-bold leading-snug">{{ $b->judul }}</div>
              <span class="inline-block mt-1 text-[11px] bg-green-50 text-green-800 px-2 py-0.5 rounded">{{ $b->kondisi }}</span>
              <div class="text-xs text-gray-500 mt-2">📍 {{ $b->lokasi }}</div>
              <div class="text-xs text-gray-400 mt-0.5">{{ $b->created_at->locale('id')->diffForHumans() }}</div>
            </div>
          </a>
        @empty
          <p class="col-span-full text-gray-500">Belum ada barang.</p>
        @endforelse
      </div>
    </section>

    {{-- Sidebar --}}
    <aside class="space-y-4">
      <div class="bg-green-50 rounded-2xl p-4">
        <div class="flex justify-between items-center mb-3">
          <h2 class="font-extrabold">📦 Kebutuhan Mendesak</h2>
          <a href="/kebutuhan" class="text-xs text-green-800 font-semibold">Lihat Semua →</a>
        </div>
        <div class="space-y-2">
          @foreach ($mendesak as $m)
            <div class="bg-white rounded-xl p-3 flex justify-between items-start gap-2 hover:shadow-sm transition">
              <div>
                <div class="text-sm font-bold">{{ $m->judul }}</div>
                <div class="text-xs text-gray-500">{{ $m->keterangan }}</div>
                <div class="text-xs text-gray-400 mt-1">📍 {{ $m->lokasi }}</div>
              </div>
              <span class="text-[11px] bg-green-100 text-green-800 font-semibold px-2 py-0.5 rounded-full">Mendukung</span>
            </div>
          @endforeach
        </div>
      </div>

      <div class="relative overflow-hidden bg-gradient-to-br from-green-100 to-green-50 rounded-2xl p-5">
        <div class="absolute -right-6 -bottom-6 text-8xl opacity-30">🤲</div>
        <h3 class="relative font-extrabold text-lg">Yuk, jadi bagian dari kebaikan ini!</h3>
        <p class="relative text-sm text-gray-600 mt-1">Setiap barang yang kamu bagikan bisa menjadi harapan baru bagi orang lain.</p>
        @if (! auth()->check() || auth()->user()->role === 'donatur')
        <a href="/berbagi" class="relative inline-block mt-3 bg-green-800 text-white text-sm font-semibold px-4 py-2 rounded-xl hover:bg-green-900 transition">Mulai Berbagi Sekarang →</a>
        @endif
      </div>

      <div class="bg-white rounded-2xl p-4 grid-cols-3 menjadi grid-cols-2">
        <div class="px-1"><div class="text-2xl">🎁</div><div class="font-extrabold text-green-800">{{ number_format($stat['barang'], 0, ',', '.') }}</div><div class="text-[11px] text-gray-500">Barang Dibagikan</div></div>
        <div class="px-1"><div class="text-2xl">👥</div><div class="font-extrabold text-green-800">{{ number_format($stat['penerima'], 0, ',', '.') }}</div><div class="text-[11px] text-gray-500">Penerima Manfaat</div></div>
      </div>
    </aside>
  </div>

  {{-- Nilai-nilai --}}
  @php
    $nilai = [
      ['🛡️', 'Aman & Terpercaya', 'Profil pengguna diverifikasi dan ada fitur pelaporan.'],
      ['🌿', 'Ramah Lingkungan', 'Mengurangi sampah dan memanfaatkan barang yang masih layak.'],
      ['👥', 'Untuk Semua', 'Siapa pun bisa berbagi dan menerima bantuan.'],
      ['💚', 'Bersama Lebih Baik', 'Karena kebaikan selalu berawal dari hal kecil.'],
    ];
  @endphp
  <section class="bg-green-50 rounded-2xl p-6 grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @foreach ($nilai as [$ic, $jd, $ket])
      <div class="flex gap-3">
        <div class="w-11 h-11 shrink-0 rounded-full bg-green-700 flex items-center justify-center text-lg">{{ $ic }}</div>
        <div class="text-sm"><b>{{ $jd }}</b><p class="text-gray-500 text-xs mt-0.5">{{ $ket }}</p></div>
      </div>
    @endforeach
  </section>
</main>

@include('partials.footer')
@include('partials.toast')
@include('partials.interaksi')
</body>
</html>