@php $selamat = session()->pull('selamat'); @endphp
@if ($selamat)
  <div id="toastSelamat" class="fixed top-4 right-4 z-50 w-[calc(100%-2rem)] max-w-xs bg-white border border-green-200 shadow-lg rounded-xl p-4 flex gap-3 transition-all duration-500">
    <div class="text-2xl">👋</div>
    <div class="flex-1 text-sm">
      <div class="font-bold text-green-800">{{ $selamat['judul'] }}</div>
      @if (! empty($selamat['info']))
        <div class="text-xs text-gray-500 mt-0.5">{{ $selamat['info'] }}</div>
      @endif
    </div>
    <button type="button" onclick="this.parentElement.remove()" class="text-gray-400 hover:text-gray-600 self-start">✕</button>
  </div>
  <script>
    setTimeout(() => {
      const t = document.getElementById('toastSelamat');
      if (t) {
        t.classList.add('opacity-0', '-translate-y-2');
        setTimeout(() => t.remove(), 500);
      }
    }, 5000);
  </script>
@endif