@extends('layouts.situs')
@section('judul', $barang->judul . ' - BerbagiYuk')
@section('isi')
  @php
    [$boleh, $pesan] = $barang->statusPengajuan(auth()->user());
    $aktif = auth()->check()
        ? \App\Models\Permintaan::where('penerima_id', auth()->id())->whereIn('status', ['menunggu', 'disetujui'])->get()
        : collect();
    $sudah = $aktif->contains('barang_id', $barang->id);
    $penuh = $aktif->count() >= 3;
    $ikon  = ['pakaian','buku','furnitur','rumah','sekolah','laptop','makanan','lainnya'];
    $warna = ['bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-orange-100 text-orange-700','bg-purple-100 text-purple-700','bg-rose-100 text-rose-700','bg-cyan-100 text-cyan-700','bg-red-100 text-red-700','bg-gray-100 text-gray-600'];
  @endphp

  <a href="/cari" class="inline-block mb-4 text-sm text-green-800 font-semibold">← Kembali ke pencarian</a>

  @php
  $galeri = $barang->fotos->pluck('path');
  if ($galeri->isEmpty() && $barang->foto) { $galeri = collect([$barang->foto]); }
@endphp
<div data-galeri-root>
  <div class="h-72 relative flex items-center justify-center overflow-hidden {{ $warna[$barang->kategori_id - 1] ?? 'bg-gray-100 text-gray-600' }}">
    @if ($galeri->isNotEmpty())
      <a data-galeri-link href="{{ asset('storage/'.$galeri->first()) }}" target="_blank" rel="noopener" aria-label="Buka foto barang ukuran penuh" class="absolute inset-0 cursor-zoom-in">
        <img data-galeri-utama src="{{ asset('storage/'.$galeri->first()) }}" class="h-full w-full object-cover" alt="Foto {{ $barang->judul }}">
      </a>
      @if ($galeri->count() > 1)
        <button type="button" data-galeri-arah="-1" aria-label="Foto sebelumnya" class="galeri-panah galeri-panah-kiri">‹</button>
        <button type="button" data-galeri-arah="1" aria-label="Foto berikutnya" class="galeri-panah galeri-panah-kanan">›</button>
      @endif
    @else
      <x-ikon :nama="$ikon[$barang->kategori_id - 1] ?? 'paket'" class="w-20 h-20" />
    @endif
  </div>
  @if ($galeri->count() > 1)
    <div class="flex gap-2 overflow-x-auto p-3">
      @foreach ($galeri as $f)
        <button type="button" data-galeri-slide="{{ $loop->index }}" aria-label="Tampilkan foto {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" class="shrink-0 overflow-hidden rounded-lg border-2 {{ $loop->first ? 'border-green-700' : 'border-transparent' }}">
          <img src="{{ asset('storage/'.$f) }}" class="h-16 w-20 object-cover" alt="">
        </button>
      @endforeach
    </div>
  @endif
</div>
      <div class="p-6 space-y-3">
        <div class="flex flex-wrap gap-2 text-xs">
          <span class="bg-green-100 text-green-800 font-semibold px-2.5 py-1 rounded-full">{{ $barang->kategori->nama }}</span>
          <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full">{{ $barang->labelKondisi() }}</span>
        </div>
        <h1 class="text-2xl font-extrabold">{{ $barang->judul }}</h1>
        <div class="flex items-center gap-1.5 text-sm text-gray-500">
          <x-ikon nama="lokasi" class="w-4 h-4 text-gray-400" />
          <span>{{ $barang->lokasi }} · dibagikan {{ $barang->created_at->locale('id')->diffForHumans() }}
          oleh {{ \Illuminate\Support\Str::before($barang->user->name, ' ') }}</span>
        </div>
        @if ($barang->donasi?->kebutuhanMendesak)
          <div class="rounded-xl border border-orange-200 bg-orange-50 p-3 text-sm text-orange-900">
            Donasi ini ditujukan untuk kampanye <b>{{ $barang->donasi->kebutuhanMendesak->judul }}</b> di {{ $barang->donasi->kebutuhanMendesak->lokasi }}.
            <a href="/kebutuhan" class="ml-1 font-semibold underline">Lihat kebutuhan</a>
          </div>
        @endif
        <p class="text-gray-700 whitespace-pre-line">{{ $barang->deskripsi }}</p>
        @if ($barang->isMakanan())
          <div class="rounded-xl bg-amber-50 p-3 text-sm text-amber-900 space-y-1">
            <div><b>Batas konsumsi:</b> {{ $barang->tanggal_kedaluwarsa ? $barang->tanggal_kedaluwarsa->format('d/m/Y') : 'Belum diisi' }}</div>
            <div><b>Penyimpanan:</b> {{ $barang->cara_penyimpanan ?: 'Belum diisi' }}</div>
          </div>
        @endif
      </div>
    </div>

    <aside class="space-y-4">
      <div class="bg-white rounded-2xl shadow-sm p-5 space-y-3">
        <h2 class="font-extrabold">Boleh diambil oleh</h2>
        <div class="flex flex-wrap gap-2">
          @foreach ($barang->syarat() as $s)
            <span class="text-xs bg-green-50 text-green-800 px-2.5 py-1 rounded-full">{{ \App\Models\Barang::JENIS[$s] }}</span>
          @endforeach
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-sm p-5 space-y-3">
        @if ($boleh && $sudah)
          <div class="bg-yellow-50 text-yellow-800 text-sm rounded-lg p-3">Kamu sudah mengajukan permintaan untuk barang ini.</div>
          <a href="/permintaan-saya" class="inline-block text-sm text-green-800 font-semibold">Lihat status permintaan →</a>
        @elseif ($boleh && $penuh)
          <div class="bg-yellow-50 text-yellow-800 text-sm rounded-lg p-3">Kamu sudah memiliki 3 permintaan aktif. Tunggu salah satunya selesai atau batalkan dulu.</div>
          <a href="/permintaan-saya" class="inline-block text-sm text-green-800 font-semibold">Lihat permintaanku →</a>
        @elseif ($boleh)
          <form method="POST" action="/barang/{{ $barang->id }}/ajukan" class="space-y-3">
            @csrf
            <label class="text-sm font-semibold">Mengapa kamu membutuhkan barang ini?</label>
            <textarea name="pesan" rows="4" required minlength="10" maxlength="500" placeholder="Ceritakan singkat kebutuhanmu..." class="w-full border rounded-lg px-3 py-2 text-sm">{{ old('pesan') }}</textarea>
            @error('pesan') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            <button class="w-full bg-green-800 text-white py-2.5 rounded-xl font-semibold hover:bg-green-900 transition">Ajukan Permintaan</button>
            <p class="text-xs text-gray-500">Admin akan memeriksa permintaanmu. Permintaan aktif: {{ $aktif->count() }}/3.</p>
          </form>
        @else
          <button disabled class="w-full bg-gray-200 text-gray-500 py-2.5 rounded-xl font-semibold cursor-not-allowed">Ajukan Permintaan</button>
          <p class="text-sm text-gray-600">{{ $pesan }}</p>
          @guest
            <a href="/masuk" class="inline-block text-sm text-green-800 font-semibold">Masuk atau daftar →</a>
          @endguest
          @if (auth()->check() && auth()->user()->role === 'penerima' && auth()->user()->status_penerima !== 'disetujui')
            <a href="/status-penerima" class="inline-block text-sm text-green-800 font-semibold">Lihat status verifikasi →</a>
          @endif
        @endif
      </div>
    </aside>
  </div>
@endsection