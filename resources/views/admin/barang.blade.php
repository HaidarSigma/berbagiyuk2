@extends('admin.layout')
@section('judul', 'Verifikasi Barang')
@section('isi')
  <div class="bg-green-50 rounded-2xl p-4 text-sm mb-5">
    <div class="font-semibold mb-1">Aturan barang yang boleh dibagikan</div>
    <ul class="list-disc ml-5 text-gray-600 space-y-0.5">
      @foreach (config('aturan.umum') as $a)
        <li>{{ $a }}</li>
      @endforeach
      @foreach (config('aturan.dilarang') as $a)
        <li class="text-red-700">Dilarang: {{ $a }}</li>
      @endforeach
    </ul>
    <a href="/aturan" target="_blank" class="inline-block mt-2 text-xs font-semibold text-green-800">Lihat halaman Aturan Barang →</a>
  </div>

  <div class="space-y-4">
    @forelse ($barang as $b)
      <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col lg:flex-row gap-4">
       <div class="lg:w-64 shrink-0 space-y-2">
  @php $galeri = $b->fotos->isNotEmpty() ? $b->fotos->pluck('path') : collect([$b->foto])->filter(); @endphp
  <div class="grid grid-cols-3 gap-1">
    @forelse ($galeri as $f)
      <a target="_blank" href="{{ asset('storage/'.$f) }}"><img src="{{ asset('storage/'.$f) }}" class="h-20 w-full object-cover rounded" alt=""></a>
    @empty
      <div class="col-span-3 h-24 bg-gray-200 rounded"></div>
    @endforelse
  </div>
  @if ($b->video)
    <video controls preload="metadata" class="w-full max-h-48 rounded-lg bg-black"><source src="/media/video/{{ $b->video->id }}"></video>
  @else
    <div class="text-xs text-yellow-700 bg-yellow-50 rounded p-2">Belum ada video (barang lama).</div>
  @endif
</div>

        <div class="flex-1 space-y-1">
          <div class="font-extrabold">{{ $b->judul }}</div>
           @if ($b->donasi_id)<span class="text-[11px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Donasi #{{ $b->donasi_id }}</span>@endif
          <div class="flex flex-wrap items-center gap-1 text-sm text-gray-500">
            <span>{{ $b->kategori->nama }} · {{ $b->labelKondisi() }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif ·</span>
            <span class="inline-flex items-center gap-1"><x-ikon nama="lokasi" class="w-3.5 h-3.5 text-gray-400" /> {{ $b->lokasi }}</span>
          </div>
          @if ($b->donasi?->kebutuhanMendesak)
            <p class="text-xs font-semibold text-orange-800">Kampanye bantuan: {{ $b->donasi->kebutuhanMendesak->judul }} — {{ $b->donasi->kebutuhanMendesak->lokasi }}</p>
          @endif
          <div class="text-xs text-gray-400">oleh {{ $b->user->name }} · {{ $b->created_at->locale('id')->diffForHumans() }}</div>
          @if ($b->deskripsi)<p class="text-sm text-gray-600 pt-1">{{ $b->deskripsi }}</p>@endif
          @if ($b->isMakanan())
            <p class="text-xs text-amber-800">Batas konsumsi: {{ $b->tanggal_kedaluwarsa ? $b->tanggal_kedaluwarsa->format('d/m/Y') : 'Belum diisi' }} · Penyimpanan: {{ $b->cara_penyimpanan ?: 'Belum diisi' }}</p>
          @endif
          @if ($b->latitude)
            <a target="_blank" href="https://www.google.com/maps?q={{ $b->latitude }},{{ $b->longitude }}" class="inline-flex items-center gap-1 text-sm text-green-800 font-semibold pt-1"><x-ikon nama="peta" class="w-4 h-4" /> Lihat titik di peta →</a>
          @endif
        </div>

        <div class="lg:w-64 space-y-2">
          <form method="POST" action="/admin/barang/{{ $b->id }}/setujui">@csrf
            <button class="w-full inline-flex items-center justify-center gap-1.5 bg-green-700 text-white py-2 rounded-lg text-sm font-semibold hover:bg-green-800"><x-ikon nama="cek" class="w-4 h-4" /> Setujui</button>
          </form>
          <form method="POST" action="/admin/barang/{{ $b->id }}/tolak" class="space-y-2">@csrf
            <textarea name="alasan_tolak" rows="2" required placeholder="Alasan penolakan (wajib)" class="w-full border rounded-lg px-3 py-2 text-sm"></textarea>
            <button class="w-full inline-flex items-center justify-center gap-1.5 bg-red-600 text-white py-2 rounded-lg text-sm font-semibold hover:bg-red-700"><x-ikon nama="tutup" class="w-4 h-4" /> Tolak</button>
          </form>
        </div>
      </div>
    @empty
      <p class="text-gray-500">Tidak ada barang yang menunggu verifikasi.</p>
    @endforelse
  </div>
@endsection