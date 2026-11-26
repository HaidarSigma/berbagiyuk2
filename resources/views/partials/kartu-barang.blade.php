@php
  $ikon  = ['pakaian','buku','furnitur','rumah','sekolah','laptop','makanan','lainnya'];
  $warna = ['bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-orange-100 text-orange-700','bg-purple-100 text-purple-700','bg-rose-100 text-rose-700','bg-cyan-100 text-cyan-700','bg-red-100 text-red-700','bg-gray-100 text-gray-600'];
@endphp
<a href="/barang/{{ $b->id }}" class="group bg-white rounded-2xl shadow-sm hover:shadow-lg hover:-translate-y-1 transition overflow-hidden">
  <div class="h-32 relative flex items-center justify-center {{ $warna[$b->kategori_id - 1] ?? 'bg-gray-100 text-gray-600' }}">
    @if ($b->foto)
      <img src="{{ asset('storage/'.$b->foto) }}" class="absolute inset-0 w-full h-full object-cover">
    @else
      <x-ikon :nama="$ikon[$b->kategori_id - 1] ?? 'paket'" class="w-11 h-11 group-hover:scale-110 transition" />
    @endif
    <span class="absolute bottom-2 left-2 bg-green-700 text-white text-[11px] font-semibold px-2 py-0.5 rounded-md">Tersedia</span>
  </div>
  <div class="p-3">
    <div class="text-sm font-bold leading-snug">{{ $b->judul }}</div>
    @if ($b->donasi?->kebutuhanMendesak)
      <span class="inline-block mt-1 text-[11px] bg-orange-50 text-orange-800 px-2 py-0.5 rounded">Kampanye: {{ $b->donasi->kebutuhanMendesak->judul }}</span>
    @endif
    <span class="inline-block mt-1 text-[11px] bg-green-50 text-green-800 px-2 py-0.5 rounded">{{ $b->labelKondisi() }}</span>
    <div class="flex items-center gap-1 text-xs text-gray-500 mt-2"><x-ikon nama="lokasi" class="w-3.5 h-3.5 text-gray-400" /> <span>{{ $b->lokasi }}</span></div>
    <div class="text-xs text-gray-400 mt-0.5">{{ $b->created_at->locale('id')->diffForHumans() }}</div>
  </div>
</a>