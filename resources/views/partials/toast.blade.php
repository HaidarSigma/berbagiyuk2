@php $selamat = session()->pull('selamat'); @endphp
@if ($selamat)
  <div id="toastSelamat" class="fixed top-4 right-4 z-50 w-[calc(100%-2rem)] max-w-xs bg-white border border-green-200 shadow-lg rounded-xl p-4 flex gap-3 transition-all duration-500">
    <div class="w-9 h-9 rounded-full bg-green-100 text-green-700 flex items-center justify-center shrink-0"><x-ikon nama="tunas" class="w-5 h-5" /></div>
    <div class="flex-1 text-sm">
      <div class="font-bold text-green-800">{{ $selamat['judul'] }}</div>
      @if (! empty($selamat['info']))
        <div class="text-xs text-gray-500 mt-0.5">{{ $selamat['info'] }}</div>
      @endif
    </div>
    <button type="button" data-tutup class="text-gray-400 hover:text-gray-600 self-start" aria-label="Tutup"><x-ikon nama="tutup" class="w-4 h-4" /></button>
  </div>
  <script nonce="{{ $cspNonce ?? '' }}">
    setTimeout(() => {
      const t = document.getElementById('toastSelamat');
      if (t) {
        t.classList.add('opacity-0', '-translate-y-2');
        setTimeout(() => t.remove(), 500);
      }
    }, 5000);
  </script>
@endif