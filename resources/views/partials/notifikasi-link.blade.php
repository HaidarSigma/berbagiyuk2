<a href="{{ route('notifikasi.index') }}"
   class="{{ $kelas ?? 'inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm text-gray-700 hover:bg-gray-100' }}"
   data-notifikasi-poll
   data-url="{{ route('notifikasi.status') }}"
   data-jumlah="{{ $jumlahNotifikasi }}"
   data-terbaru="{{ $notifikasiTerbaru?->id ?? '' }}">
  <x-ikon nama="bel" class="w-4 h-4 text-gray-500" />
  <span>Notifikasi</span>
  <span data-notifikasi-badge class="ml-auto rounded-full bg-red-500 px-2 py-0.5 text-[11px] font-semibold text-white {{ $jumlahNotifikasi > 0 ? '' : 'hidden' }}">{{ $jumlahNotifikasi }}</span>
</a>
