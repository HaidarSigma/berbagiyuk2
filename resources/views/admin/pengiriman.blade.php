@extends('admin.layout')
@section('judul', 'Riwayat Pengiriman')
@section('isi')
  @php
    $label = [
      'baru'       => ['Belum diambil petugas', 'bg-yellow-100 text-yellow-800'],
      'ditugaskan' => ['Menuju donatur', 'bg-blue-100 text-blue-800'],
      'diambil'    => ['Menuju penerima', 'bg-indigo-100 text-indigo-800'],
    ];
    $durasi = fn ($m) => $m === null ? '-' : (intdiv((int) $m, 60) > 0 ? intdiv((int) $m, 60).' j ' : '').((int) $m % 60).' m';
    $kartu = [
      ['Total terkirim', $total, 'centang-lingkaran', 'bg-green-100 text-green-700'],
      ['Terkirim bulan ini', $bulanIni, 'kalender', 'bg-blue-100 text-blue-700'],
      ['Rata-rata durasi', $durasi($rata), 'jam', 'bg-purple-100 text-purple-700'],
      ['Sedang berlangsung', $berlangsung, 'truk', 'bg-yellow-100 text-yellow-700'],
    ];
  @endphp

  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
    @foreach ($kartu as [$nama, $nilai, $ic, $warna])
      <div class="bg-white rounded-2xl p-4 shadow-sm">
        <div class="w-10 h-10 rounded-xl {{ $warna }} flex items-center justify-center"><x-ikon :nama="$ic" class="w-5 h-5" /></div>
        <div class="text-2xl font-extrabold mt-3">{{ $nilai }}</div>
        <div class="text-xs text-gray-500">{{ $nama }}</div>
      </div>
    @endforeach
  </div>

  @if ($errors->any())
    <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">@foreach (array_unique($errors->all()) as $e)<p>{{ $e }}</p>@endforeach</div>
  @endif

  <div class="flex gap-2 mb-4 text-sm">
    <a href="/admin/pengiriman?tab=selesai" class="px-4 py-2 rounded-full {{ $tab === 'selesai' ? 'bg-green-800 text-white font-semibold' : 'bg-white text-gray-600' }}">Terkirim</a>
    <a href="/admin/pengiriman?tab=berlangsung" class="px-4 py-2 rounded-full {{ $tab === 'berlangsung' ? 'bg-green-800 text-white font-semibold' : 'bg-white text-gray-600' }}">Berlangsung</a>
  </div>

  <form method="GET" class="bg-white rounded-2xl shadow-sm p-4 grid sm:grid-cols-2 lg:grid-cols-6 gap-3 mb-5">
    <input type="hidden" name="tab" value="{{ $tab }}">
    <input name="q" value="{{ request('q') }}" placeholder="Cari barang, donatur, atau penerima..." class="lg:col-span-2 border rounded-lg px-3 py-2 text-sm">
    <select name="petugas" class="border rounded-lg px-3 py-2 text-sm">
      <option value="">Semua petugas</option>
      @foreach ($petugas as $p)<option value="{{ $p->id }}" @selected(request('petugas') == $p->id)>{{ $p->name }}</option>@endforeach
    </select>
    <input type="date" name="dari" value="{{ request('dari') }}" class="border rounded-lg px-3 py-2 text-sm" title="Dari tanggal">
    <input type="date" name="sampai" value="{{ request('sampai') }}" class="border rounded-lg px-3 py-2 text-sm" title="Sampai tanggal">
    <div class="flex gap-2">
      <button class="flex-1 bg-green-800 text-white rounded-lg px-4 py-2 text-sm font-semibold">Terapkan</button>
      <a href="/admin/pengiriman?tab={{ $tab }}" class="border rounded-lg px-3 py-2 text-sm text-gray-600">Reset</a>
    </div>
  </form>

  @if ($tab === 'selesai')
    <div class="flex justify-end mb-2">
      <a href="/admin/pengiriman/ekspor?{{ http_build_query(request()->only('q', 'petugas', 'dari', 'sampai')) }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-green-800"><x-ikon nama="unduh" class="w-4 h-4" /> Ekspor CSV</a>
    </div>
  @endif

  <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-xs text-gray-500 border-b">
        <tr>
          <th class="p-3">{{ $tab === 'selesai' ? 'Selesai' : 'Status' }}</th>
          <th class="p-3">Barang</th>
          <th class="p-3">Donatur → Penerima</th>
          <th class="p-3">Petugas</th>
          <th class="p-3">{{ $tab === 'selesai' ? 'Durasi' : 'Sejak' }}</th>
          <th class="p-3">Bukti</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($daftar as $t)
          @php
            $b = $t->permintaan->barang;
            $sejak = $t->diambil_at ?? $t->ditugaskan_at ?? $t->created_at;
            $telat = $t->status === 'baru' && $t->created_at->lt(now()->subDay());
          @endphp
          <tr class="border-b last:border-0 align-top">
            <td class="p-3 whitespace-nowrap">
              @if ($tab === 'selesai')
                {{ $t->selesai_at->locale('id')->translatedFormat('d M Y') }}
                <div class="text-xs text-gray-400">{{ $t->selesai_at->format('H:i') }}</div>
              @else
                <span class="text-xs px-2 py-0.5 rounded-full {{ $label[$t->status][1] }}">{{ $label[$t->status][0] }}</span>
              @endif
            </td>
            <td class="p-3">
              <div class="font-semibold">{{ $b->judul }}</div>
              <div class="text-xs text-gray-500">{{ $b->kategori->nama }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif</div>
              @if ($b->donasi?->kebutuhanMendesak)
                <div class="text-xs font-semibold text-orange-800">Kampanye: {{ $b->donasi->kebutuhanMendesak->judul }}</div>
              @endif
              @if ($t->catatan)<div class="text-xs text-gray-500 mt-1">Catatan: {{ $t->catatan }}</div>@endif
            </td>
            <td class="p-3 text-xs space-y-0.5">
              <div class="flex items-center gap-1"><x-ikon nama="naik" class="w-3.5 h-3.5 text-gray-400" /> <span>{{ $b->user->name }} <span class="text-gray-400">({{ $b->lokasi }})</span></span></div>
              <div class="flex items-center gap-1"><x-ikon nama="turun" class="w-3.5 h-3.5 text-green-700" /> <span>{{ $t->permintaan->penerima->name }}</span></div>
            </td>
            <td class="p-3">{{ $t->petugas->name ?? '-' }}</td>
            <td class="p-3 whitespace-nowrap {{ $telat ? 'text-red-600 font-semibold' : '' }}">
              @if ($tab === 'selesai')
                {{ $durasi($t->ditugaskan_at && $t->selesai_at ? $t->ditugaskan_at->diffInMinutes($t->selesai_at) : null) }}
              @else
                {{ $sejak->locale('id')->diffForHumans() }}
              @endif
            </td>
            <td class="p-3 text-xs space-x-2 whitespace-nowrap">
              @if ($t->foto_ambil)<a target="_blank" href="/bukti/{{ $t->id }}/ambil" class="inline-flex items-center gap-1 text-green-800 font-semibold"><x-ikon nama="kamera" class="w-3.5 h-3.5" /> Ambil</a>@endif
              @if ($t->foto_serah)<a target="_blank" href="/bukti/{{ $t->id }}/serah" class="inline-flex items-center gap-1 text-green-800 font-semibold"><x-ikon nama="kamera" class="w-3.5 h-3.5" /> Serah</a>@endif
              @if (! $t->foto_ambil && ! $t->foto_serah)<span class="text-gray-400">-</span>@endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="p-4 text-gray-500">{{ $tab === 'selesai' ? 'Belum ada pengiriman yang selesai.' : 'Tidak ada tugas yang sedang berlangsung.' }}</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $daftar->links() }}</div>
@endsection