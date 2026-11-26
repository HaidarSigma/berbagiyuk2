@php $kelas = ($melayang ?? false) ? 'fixed bottom-4 left-4 z-40 shadow-lg' : ''; @endphp
<button type="button" data-tema aria-label="Ganti tema terang atau gelap" title="Ganti tema"
        class="{{ $kelas }} w-9 h-9 shrink-0 rounded-full border border-gray-300 bg-white hover:bg-gray-50 flex items-center justify-center">
  <x-ikon nama="bulan" class="tema-ikon-gelap w-4 h-4 text-gray-700" />
  <x-ikon nama="matahari" class="tema-ikon-terang w-4 h-4 text-yellow-500" />
</button>