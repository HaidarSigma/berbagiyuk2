<!DOCTYPE html>
<html lang="id" class="overflow-y-scroll">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barang Saya - BerbagiYuk</title>
  @include('partials.head-asset')
</head>
<body class="bg-gray-50 p-6">
  <div class="max-w-3xl mx-auto space-y-4">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-green-800">Barang Saya</h1>
      <div class="flex gap-3 text-sm items-center">
        @include('partials.notifikasi-link', ['kelas' => 'inline-flex items-center gap-2 text-green-800'])
        <a href="/berbagi" class="bg-green-800 text-white px-4 py-2 rounded-lg">+ Bagikan Barang</a>
        <a href="/" class="text-green-800">← Beranda</a>
      </div>
    </div>


    @if (session('status'))
      <div class="bg-green-50 text-green-800 text-sm rounded-lg p-3">{{ session('status') }}</div>
    @endif

    @php
      $label = [
        'menunggu'  => ['Menunggu verifikasi', 'bg-yellow-100 text-yellow-800'],
        'disetujui' => ['Disetujui', 'bg-green-100 text-green-800'],
        'ditolak'   => ['Ditolak', 'bg-red-100 text-red-700'],
      ];
    @endphp

    @forelse ($barang as $b)
      <div id="barang-{{ $b->id }}" class="bg-white rounded-xl p-4 shadow-sm flex gap-4">
        <div class="w-24 h-24 shrink-0 rounded-lg bg-gray-200 overflow-hidden">
          @if ($b->foto)<img src="{{ asset('storage/'.$b->foto) }}" class="w-full h-full object-cover">@endif
        </div>
        <div class="flex-1">
          <div class="flex justify-between items-start gap-2">
            <div class="font-semibold">{{ $b->judul }}</div>
             @if ($b->donasi_id)<span class="text-[11px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Donasi #{{ $b->donasi_id }}</span>@endif
            @if ($b->status === 'ditarik')
              <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Ditarik</span>
            @else
              <span class="text-xs px-2 py-0.5 rounded-full {{ $label[$b->verifikasi][1] }}">{{ $label[$b->verifikasi][0] }}</span>
            @endif
          </div>
          <div class="flex flex-wrap items-center gap-1.5 text-sm text-gray-500">
            <span>{{ $b->kategori->nama }} · {{ $b->labelKondisi() }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif ·</span>
            <span class="inline-flex items-center gap-1"><x-ikon nama="lokasi" class="w-3.5 h-3.5 text-gray-400" /> {{ $b->lokasi }}</span>
          </div>
          @if ($b->donasi?->kebutuhanMendesak)
            <p class="text-xs font-semibold text-orange-800">Kampanye bantuan: {{ $b->donasi->kebutuhanMendesak->judul }} — {{ $b->donasi->kebutuhanMendesak->lokasi }}</p>
          @endif
          <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-1">
        ID:
      <code class="bg-gray-100 rounded px-1.5 py-0.5 select-all break-all">{{ $b->token }}</code>
  <button type="button" data-salin="{{ $b->token }}" class="text-green-800 font-semibold">Salin</button>
</div>
<div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mt-1">
  <span class="inline-flex items-center gap-1"><x-ikon nama="gambar" class="w-3.5 h-3.5 text-gray-400" /> {{ $b->fotos->count() ?: ($b->foto ? 1 : 0) }} foto</span>
  @if ($b->fotos->isNotEmpty())
    @foreach ($b->fotos as $foto)
      <a target="_blank" rel="noopener" href="{{ asset('storage/'.$foto->path) }}" aria-label="Lihat foto {{ $loop->iteration }} untuk {{ $b->judul }}" class="block w-10 h-10 rounded overflow-hidden border border-gray-200">
        <img src="{{ asset('storage/'.$foto->path) }}" class="w-full h-full object-cover" alt="Foto {{ $loop->iteration }} {{ $b->judul }}">
      </a>
    @endforeach
  @elseif ($b->foto)
    <a target="_blank" rel="noopener" href="{{ asset('storage/'.$b->foto) }}" aria-label="Lihat foto {{ $b->judul }}" class="block w-10 h-10 rounded overflow-hidden border border-gray-200">
      <img src="{{ asset('storage/'.$b->foto) }}" class="w-full h-full object-cover" alt="Foto {{ $b->judul }}">
    </a>
  @endif
  @if ($b->video)
    <a target="_blank" rel="noopener" href="/media/video/{{ $b->video->id }}" class="inline-flex items-center gap-1 text-green-800 font-semibold"><x-ikon nama="video" class="w-3.5 h-3.5" /> Lihat video</a>
  @else
    <span>belum ada video</span>
  @endif
</div>
          @php
            if ($b->status === 'ditarik') {
              $statusTahapan = [
                ['judul' => 'Donasi dikirim', 'keterangan' => 'Barang berhasil diajukan.', 'status' => 'selesai'],
                ['judul' => 'Donasi ditarik', 'keterangan' => 'Donatur menarik barang ini dari proses donasi.', 'status' => 'dibatalkan'],
              ];
            } elseif ($b->verifikasi === 'ditolak') {
              $statusTahapan = [
                ['judul' => 'Donasi dikirim', 'keterangan' => 'Barang berhasil diajukan.', 'status' => 'selesai'],
                ['judul' => 'Verifikasi admin', 'keterangan' => $b->alasan_tolak ?: 'Barang belum disetujui. Kamu bisa memperbaiki dan mengajukan ulang.', 'status' => 'ditolak'],
              ];
            } else {
              $disetujui = $b->verifikasi === 'disetujui';
              $sudahDiambil = in_array($b->status, ['tersedia', 'diproses', 'selesai'], true);

              $statusTahapan = [
                ['judul' => 'Donasi dikirim', 'keterangan' => 'Barang berhasil diajukan.', 'status' => 'selesai'],
                [
                  'judul' => 'Verifikasi admin',
                  'keterangan' => $disetujui ? 'Barang disetujui.' : 'Admin sedang memeriksa barang.',
                  'status' => $disetujui ? 'selesai' : 'aktif',
                ],
                [
                  'judul' => 'Pengambilan oleh petugas',
                  'keterangan' => ! $disetujui
                    ? 'Menunggu hasil verifikasi.'
                    : ($sudahDiambil
                      ? ($b->status === 'tersedia'
                        ? 'Barang sudah diambil petugas dan disimpan di posko.'
                        : 'Barang sudah dijemput petugas; proses selanjutnya ditangani posko.')
                      : ($b->pengambilan && $b->pengambilan->status === 'ditugaskan'
                        ? 'Petugas sudah ditugaskan untuk menjemput barang.'
                        : 'Menunggu petugas menjemput barang.')),
                  'status' => ! $disetujui ? 'menunggu' : ($sudahDiambil ? 'selesai' : 'aktif'),
                ],
              ];
            }
          @endphp
          @if ($b->verifikasi === 'ditolak' && $b->alasan_tolak)
            <p class="text-sm text-red-600 mt-1">Alasan: {{ $b->alasan_tolak }}</p>
          @endif
          @include('partials.timeline-status', ['statusTahapan' => $statusTahapan])
          @if ($b->pengambilan && $b->pengambilan->foto_ambil)
            <div class="mt-3 rounded-xl bg-blue-50 p-3">
              <p class="mb-2 text-xs font-semibold text-blue-900">Foto bukti penjemputan petugas</p>
              <a href="/bukti-pengambilan/{{ $b->pengambilan->id }}" target="_blank" rel="noopener" class="inline-block">
                <img src="/bukti-pengambilan/{{ $b->pengambilan->id }}" alt="Bukti penjemputan {{ $b->judul }}" class="max-h-48 max-w-full rounded-lg border border-blue-100 object-contain">
              </a>
            </div>
          @endif

          <div class="mt-2 flex gap-4 text-sm">
            @if ($b->verifikasi === 'disetujui' && $b->status === 'menunggu_ambil')
  <a href="/barang-saya/{{ $b->id }}/label" target="_blank" class="inline-flex items-center gap-1 text-green-800 font-semibold"><x-ikon nama="tag" class="w-3.5 h-3.5" /> Label QR</a>
  @if ($b->donasi_id)
    <a href="/donasi/{{ $b->donasi_id }}/label" target="_blank" class="inline-flex items-center gap-1 text-green-800 font-semibold"><x-ikon nama="tag" class="w-3.5 h-3.5" /> Semua label donasi #{{ $b->donasi_id }}</a>
  @endif
@endif
            @if ($b->status !== 'ditarik' && in_array($b->verifikasi, ['menunggu', 'ditolak']))
              <a href="/barang-saya/{{ $b->id }}/ubah" class="inline-flex items-center gap-1 text-green-800 font-semibold">
                <x-ikon nama="edit" class="w-3.5 h-3.5" /> {{ $b->verifikasi === 'ditolak' ? 'Perbaiki & ajukan ulang' : 'Ubah' }}
              </a>
            @endif
            @if ($b->status === 'menunggu_ambil')
              <form method="POST" action="/barang-saya/{{ $b->id }}/tarik" data-konfirmasi="Tarik barang ini?">@csrf
                <button class="text-red-600">Tarik Barang</button>
              </form>
            @endif
          </div>
        </div>
      </div>
    @empty
      <p class="text-gray-500">Kamu belum membagikan barang.</p>
    @endforelse
  </div>
@include('partials.sukses')
@include('partials.interaksi')
@include('partials.notifikasi-toast')
</body>
</html>