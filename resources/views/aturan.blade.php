@extends('layouts.situs')

@section('judul', 'Aturan Barang - BerbagiYuk')

@section('isi')
  @php
    $aturan = config('aturan', []);
  @endphp

  <div class="max-w-5xl mx-auto space-y-6">
    <section class="rounded-3xl bg-gradient-to-br from-green-100 via-white to-emerald-50 border border-green-100 p-6 md:p-8 shadow-sm">
      <div>
        <span class="inline-flex items-center rounded-full bg-white px-3 py-1 text-xs font-semibold text-green-800 shadow-sm border border-green-100">Panduan donasi</span>
        <h1 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-gray-900">Aturan Barang</h1>
        @if (! auth()->check() || ! in_array(auth()->user()->role, ['admin', 'petugas'], true))
          <a href="/berbagi" class="mt-4 inline-flex items-center justify-center rounded-xl bg-green-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-900 transition">
            Ajukan barang
          </a>
        @endif
      </div>
      <p class="mt-4 max-w-2xl text-sm md:text-base text-gray-600 leading-relaxed">
        Barang yang dikirim harus aman, rapi, dan benar-benar layak dipakai. Tujuan utamanya agar proses berbagi tetap bermanfaat, adil, dan tidak mengganggu penerima.
      </p>
    </section>

    <div class="grid gap-6 lg:grid-cols-2">
      <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-3 mb-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-700"><x-ikon nama="cek" class="w-5 h-5" /></div>
          <h2 class="text-xl font-bold text-gray-900">Syarat barang</h2>
        </div>
        <ul class="space-y-3 text-sm text-gray-700">
          @foreach (($aturan['umum'] ?? []) as $item)
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-green-100 text-green-800"><x-ikon nama="cek" class="w-3 h-3" /></span>
              <span class="leading-relaxed">{{ $item }}</span>
            </li>
          @endforeach
        </ul>
      </section>

      <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
        <div class="flex items-center gap-3 mb-4">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-50 text-red-600"><x-ikon nama="tutup" class="w-5 h-5" /></div>
          <h2 class="text-xl font-bold text-gray-900">Barang dilarang</h2>
        </div>
        <ul class="space-y-3 text-sm text-gray-700">
          @foreach (($aturan['dilarang'] ?? []) as $item)
            <li class="flex gap-3">
              <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600"><x-ikon nama="tutup" class="w-3 h-3" /></span>
              <span class="leading-relaxed">{{ $item }}</span>
            </li>
          @endforeach
        </ul>
      </section>
    </div>

    <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
      <div class="mb-5">
        <h2 class="text-xl font-bold text-gray-900">Kategori yang umum diterima</h2>
        <p class="mt-1 text-sm text-gray-500">Barang yang masuk kategori ini biasanya lebih cepat diproses jika kondisi fisiknya masih layak dipakai.</p>
      </div>

      @php
        $ikonAturan = [
          'Pakaian' => 'pakaian',
          'Buku & Alat Tulis' => 'buku',
          'Furnitur' => 'furnitur',
          'Perlengkapan Rumah' => 'rumah',
          'Perlengkapan Sekolah' => 'sekolah',
          'Elektronik' => 'laptop',
          'Makanan & Minuman' => 'makanan',
          'Lainnya' => 'lainnya',
        ];
      @endphp
      <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach (($aturan['kategori'] ?? []) as $nama => [$emoji, $keterangan])
          <div class="rounded-2xl border border-gray-200 bg-gray-50 p-4">
            <div class="flex items-center gap-3">
              <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-green-700 shadow-sm"><x-ikon :nama="$ikonAturan[$nama] ?? 'paket'" class="w-5 h-5" /></div>
              <h3 class="font-semibold text-gray-900">{{ $nama }}</h3>
            </div>
            <p class="mt-3 text-sm text-gray-600 leading-relaxed">{{ $keterangan }}</p>
          </div>
        @endforeach
      </div>
    </section>

    <section class="rounded-2xl bg-white p-6 shadow-sm border border-gray-100">
      <h2 class="text-xl font-bold text-gray-900">Alur verifikasi</h2>
      <ol class="mt-5 space-y-3">
        @foreach (($aturan['proses'] ?? []) as $index => $item)
          <li class="flex gap-3 items-start">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-100 text-xs font-bold text-green-800">{{ $index + 1 }}</span>
            <span class="text-sm leading-relaxed text-gray-700">{{ $item }}</span>
          </li>
        @endforeach
      </ol>
    </section>
  </div>
@endsection
