@extends('layouts.situs')
@section('judul', 'Permintaan Saya - BerbagiYuk')
@section('isi')
  @php
    $label = [
      'menunggu'   => ['Menunggu persetujuan', 'bg-yellow-100 text-yellow-800'],
      'disetujui'  => ['Disetujui', 'bg-green-100 text-green-800'],
      'ditolak'    => ['Ditolak', 'bg-red-100 text-red-700'],
      'dibatalkan' => ['Dibatalkan', 'bg-gray-100 text-gray-600'],
    ];
    $aktif = $permintaan->whereIn('status', ['menunggu', 'disetujui'])->count();
  @endphp

  <div class="flex justify-between items-center">
    <h1 class="text-2xl font-extrabold">Permintaan Saya</h1>
    <span class="text-sm bg-white rounded-full shadow-sm px-3 py-1">Permintaan aktif: <b>{{ $aktif }}/3</b></span>
  </div>

  @if (session('status'))
    <div class="mt-4 bg-green-50 text-green-800 text-sm rounded-lg p-3">{{ session('status') }}</div>
  @endif

  <div class="mt-4 space-y-3">
    @forelse ($permintaan as $p)
      @php
        $t = $p->tugas;
        if ($p->status === 'menunggu') {
          $statusPersetujuan = 'aktif';
          $keteranganPersetujuan = 'Admin sedang memeriksa permintaanmu.';
        } elseif ($p->status === 'disetujui') {
          $statusPersetujuan = 'selesai';
          $keteranganPersetujuan = 'Permintaan disetujui.';
        } elseif ($p->status === 'ditolak') {
          $statusPersetujuan = 'ditolak';
          $keteranganPersetujuan = $p->alasan_tolak ?: 'Permintaan tidak disetujui.';
        } else {
          $statusPersetujuan = 'dibatalkan';
          $keteranganPersetujuan = 'Permintaan dibatalkan.';
        }

        $pengantaranSelesai = $p->status === 'disetujui' && $t && $t->status === 'selesai';
        $pengantaranDimulai = $p->status === 'disetujui' && $t && $t->status === 'ditugaskan';
        $statusPengantaran = $p->status !== 'disetujui'
          ? 'menunggu'
          : ($pengantaranSelesai ? 'selesai' : 'aktif');
        $keteranganPengantaran = $pengantaranSelesai
          ? 'Pengantaran sudah selesai.'
          : ($pengantaranDimulai
            ? 'Petugas sedang mengantar barang.'
            : ($p->status === 'disetujui'
              ? 'Menunggu petugas mengambil tugas pengantaran.'
              : 'Menunggu persetujuan admin.'));

        $statusTahapan = [
          ['judul' => 'Permintaan dikirim', 'keterangan' => 'Permintaanmu berhasil diajukan.', 'status' => 'selesai'],
          ['judul' => 'Persetujuan admin', 'keterangan' => $keteranganPersetujuan, 'status' => $statusPersetujuan],
          ['judul' => 'Pengantaran oleh petugas', 'keterangan' => $keteranganPengantaran, 'status' => $statusPengantaran],
          [
            'judul' => 'Barang diterima',
            'keterangan' => $pengantaranSelesai ? 'Barang sudah sampai kepadamu.' : 'Menunggu barang diterima.',
            'status' => $pengantaranSelesai ? 'selesai' : 'menunggu',
          ],
        ];
      @endphp
      <div id="permintaan-{{ $p->id }}" class="bg-white rounded-2xl shadow-sm p-4 flex gap-4">
        <div class="w-24 h-24 shrink-0 rounded-lg bg-gray-200 overflow-hidden">
          @if ($p->barang->foto)<img src="{{ asset('storage/'.$p->barang->foto) }}" class="w-full h-full object-cover">@endif
        </div>
        <div class="flex-1 space-y-1">
          <div class="flex justify-between items-start gap-2">
            <a href="/barang/{{ $p->barang->id }}" class="font-bold hover:text-green-800">{{ $p->barang->judul }}</a>
            <span class="text-xs px-2 py-0.5 rounded-full whitespace-nowrap {{ $label[$p->status][1] }}">{{ $label[$p->status][0] }}</span>
          </div>
          <div class="flex flex-wrap items-center gap-1 text-sm text-gray-500">
            <span>{{ $p->barang->kategori->nama }}
            @if ($p->status === 'disetujui' && $p->barang->jumlahLabel())
              · Donasi: {{ $p->barang->jumlahLabel() }} (diberikan utuh)
            @endif
            ·</span>
            <span class="inline-flex items-center gap-1"><x-ikon nama="lokasi" class="w-3.5 h-3.5 text-gray-400" /> {{ $p->barang->lokasi }}</span>
          </div>
          @if ($p->barang->donasi?->kebutuhanMendesak)
            <p class="text-xs font-semibold text-orange-800">Kampanye bantuan: {{ $p->barang->donasi->kebutuhanMendesak->judul }}</p>
          @endif
          <p class="text-sm text-gray-600">“{{ $p->pesan }}”</p>
          @if ($p->status === 'ditolak' && $p->alasan_tolak)
            <p class="text-sm text-red-600"><b>Alasan:</b> {{ $p->alasan_tolak }}</p>
          @endif
          @include('partials.timeline-status', ['statusTahapan' => $statusTahapan])
          @if ($p->status === 'disetujui')
  @if ($t && $t->status === 'ditugaskan')
  <div class="bg-green-50 text-green-800 text-sm rounded-lg p-3 space-y-1">
      Petugas sedang mengantar barangmu. Sebutkan kode ini kepada petugas saat barang diterima:
      <div class="text-2xl font-extrabold tracking-widest">{{ $t->kode_serah }}</div>
  </div>
  @elseif ($pengantaranSelesai)
    <div class="bg-green-50 text-green-800 text-sm rounded-lg p-3">Barang sudah kamu terima. Terima kasih!</div>
  @endif
@endif
          <div class="flex gap-4 text-xs text-gray-400 items-center pt-1">
            <span>diajukan {{ $p->created_at->locale('id')->diffForHumans() }}</span>
            @if ($p->status === 'menunggu')
              <form method="POST" action="/permintaan-saya/{{ $p->id }}/batal" data-konfirmasi="Batalkan permintaan ini?">@csrf
                <button class="text-red-600 font-semibold">Batalkan</button>
              </form>
            @endif
          </div>
        </div>
      </div>
    @empty
      <p class="text-gray-500">Kamu belum mengajukan permintaan. <a href="/cari" class="text-green-800 font-semibold">Cari barang →</a></p>
    @endforelse
  </div>
@endsection