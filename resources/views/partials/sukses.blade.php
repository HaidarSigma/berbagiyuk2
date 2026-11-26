@if (session('sukses'))
  @php
    $s     = session('sukses');
    $warna = ['#16a34a', '#f59e0b', '#22c55e', '#fbbf24', '#15803d', '#86efac'];
  @endphp
  <div id="sukses" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 p-4" role="status" aria-live="polite">
    <div class="absolute inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
      @for ($i = 0; $i < 18; $i++)
        <span class="konfeti" style="left: {{ 3 + $i * 5.4 }}%; background: {{ $warna[$i % 6] }}; animation-delay: {{ ($i % 9) * 0.09 }}s; animation-duration: {{ 1.6 + ($i % 4) * 0.25 }}s;"></span>
      @endfor
    </div>

    <div class="sukses-kartu relative bg-white rounded-3xl shadow-2xl w-full max-w-sm p-8 text-center space-y-3">
      <svg class="mx-auto w-20 h-20" viewBox="0 0 52 52" fill="none" aria-hidden="true">
        <circle class="sukses-lingkaran" cx="26" cy="26" r="25" stroke="#16a34a" stroke-width="2.5" />
        <path class="sukses-centang" d="M14 27l8 8 16-16" stroke="#16a34a" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
      </svg>
      <h2 class="text-2xl font-extrabold text-green-800">{{ $s['judul'] ?? 'Berhasil!' }}</h2>
      <p class="text-sm text-gray-600">{{ $s['pesan'] ?? '' }}</p>
      <button type="button" data-tutup-sukses class="bg-green-800 text-white rounded-xl px-8 py-2 text-sm font-semibold hover:bg-green-900">Oke</button>
    </div>
  </div>

  <script nonce="{{ $cspNonce ?? '' }}">
    (() => {
      const el = document.getElementById('sukses');
      const tutup = () => { clearTimeout(t); if (el) el.remove(); };
      const t = setTimeout(tutup, 6500);
      el.addEventListener('click', e => { if (e.target === el || e.target.closest('[data-tutup-sukses]')) tutup(); });
      document.addEventListener('keydown', e => { if (e.key === 'Escape') tutup(); });
    })();
  </script>
@endif