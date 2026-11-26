<script nonce="{{ $cspNonce ?? '' }}">
  const MAKS_FOTO_MB = 2, MAKS_VIDEO_MB = 20, MAKS_DETIK = 60;

  document.addEventListener('change', e => {
    const el = e.target;

    if (el.matches('.in-foto')) {
      const gambar = el.closest('.slot-foto').querySelector('img');
      const f = el.files[0];
      if (!f) { gambar.classList.add('hidden'); return; }
      if (f.size > MAKS_FOTO_MB * 1048576) {
        alert('Ukuran tiap foto maksimal ' + MAKS_FOTO_MB + ' MB.');
        el.value = ''; gambar.classList.add('hidden'); return;
      }
      gambar.src = URL.createObjectURL(f);
      gambar.classList.remove('hidden');
    }

    if (el.matches('.in-video')) {
      const info = el.closest('.slot-video').querySelector('.info-video');
      const f = el.files[0];
      info.textContent = '';
      if (!f) return;
      if (f.size > MAKS_VIDEO_MB * 1048576) {
        alert('Ukuran video maksimal ' + MAKS_VIDEO_MB + ' MB. Pendekkan atau kompres videonya.');
        el.value = ''; return;
      }
      const v = document.createElement('video');
      v.preload = 'metadata';
      v.onloadedmetadata = () => {
        URL.revokeObjectURL(v.src);
        if (v.duration > MAKS_DETIK) {
          alert('Video maksimal ' + MAKS_DETIK + ' detik. Videomu ' + Math.round(v.duration) + ' detik.');
          el.value = '';
        } else {
          info.textContent = 'Siap · ' + Math.round(v.duration) + ' detik · ' + (f.size / 1048576).toFixed(1) + ' MB';
        }
      };
      v.onerror = () => { info.textContent = (f.size / 1048576).toFixed(1) + ' MB'; };
      v.src = URL.createObjectURL(f);
    }
  });
</script>