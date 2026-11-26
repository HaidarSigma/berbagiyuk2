<div data-notifikasi-toast class="fixed bottom-4 right-4 z-50 hidden w-[calc(100%-2rem)] max-w-sm rounded-xl border border-blue-200 bg-white p-4 shadow-xl" role="status" aria-live="polite">
  <div class="flex items-start gap-3">
    <x-ikon nama="bel" class="w-5 h-5 text-blue-700 mt-0.5" />
    <a data-notifikasi-toast-link href="/notifikasi" class="min-w-0 flex-1">
      <span data-notifikasi-toast-judul class="block text-sm font-bold text-gray-900"></span>
      <span data-notifikasi-toast-pesan class="mt-1 block text-xs text-gray-600"></span>
      <span class="mt-2 block text-xs font-semibold text-blue-700">Lihat notifikasi →</span>
    </a>
    <button type="button" data-notifikasi-tutup class="text-gray-400 hover:text-gray-700" aria-label="Tutup notifikasi"><x-ikon nama="tutup" class="w-4 h-4" /></button>
  </div>
</div>
