<style>
  :root { --gerak: 180ms; }
  html { scroll-behavior: smooth; }
  body { -webkit-tap-highlight-color: transparent; }

  a, button, summary, input, select, textarea {
    transition: color var(--gerak) ease,
                background-color var(--gerak) ease,
                border-color var(--gerak) ease,
                box-shadow var(--gerak) ease,
                transform var(--gerak) ease,
                opacity var(--gerak) ease;
  }

  a:focus-visible, button:focus-visible, summary:focus-visible,
  input:focus-visible, select:focus-visible, textarea:focus-visible {
    outline: 3px solid rgba(34, 197, 94, .28);
    outline-offset: 2px;
  }

  button:not(:disabled):active, a:active {
    transform: translateY(1px) scale(.985);
  }

  nav a, aside a {
    position: relative;
  }

  .bg-white.rounded-2xl.shadow-sm,
  .bg-white.rounded-xl.shadow-sm {
    transition: transform var(--gerak) ease, box-shadow var(--gerak) ease, border-color var(--gerak) ease;
  }

  @media (hover: hover) {
    .bg-white.rounded-2xl.shadow-sm:hover,
    .bg-white.rounded-xl.shadow-sm:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px rgba(22, 101, 52, .09);
    }
  }

  details > summary { cursor: pointer; }
  details[open] > :not(summary) {
    animation: bukaKonten 180ms ease-out;
  }
  @keyframes bukaKonten {
    from { opacity: 0; transform: translateY(-5px); }
    to { opacity: 1; transform: translateY(0); }
  }

  input, select, textarea {
    transition: border-color var(--gerak) ease, box-shadow var(--gerak) ease, background-color var(--gerak) ease;
  }
  input:focus, select:focus, textarea:focus {
    border-color: #86efac !important;
  }

  .muncul-saat-scroll {
    opacity: 0;
    transform: translateY(10px);
  }
  .muncul-saat-scroll.tampak {
    opacity: 1;
    transform: translateY(0);
    transition: opacity 420ms ease, transform 420ms ease;
  }

  .riak {
    position: fixed;
    border-radius: 9999px;
    background: rgba(255, 255, 255, .38);
    pointer-events: none;
    z-index: 9999;
    transform: scale(0);
    animation: riakTombol 500ms ease-out;
  }
  @keyframes riakTombol {
    to { transform: scale(4); opacity: 0; }
  }

  @media (prefers-reduced-motion: reduce) {
    html { scroll-behavior: auto; }
    *, *::before, *::after {
      animation-duration: 1ms !important;
      transition-duration: 1ms !important;
    }
  }
</style>

<script nonce="{{ $cspNonce ?? '' }}">
  (() => {
    document.addEventListener('click', event => {
      const tombol = event.target.closest('[data-tutup]');
      if (!tombol) return;
      const elemen = tombol.parentElement;
      if (!elemen) return;
      elemen.classList.add('opacity-0', '-translate-y-2');
      setTimeout(() => elemen.remove(), 500);
    });

    document.addEventListener('click', event => {
      const gambar = event.target.closest('[data-galeri]');
      if (!gambar) return;
      const utama = document.getElementById(gambar.dataset.galeri);
      if (utama) utama.src = gambar.currentSrc || gambar.src;
    });

    document.addEventListener('pointerdown', event => {
      const elemen = event.target.closest('button, a');
      if (!elemen) return;
      const rect = elemen.getBoundingClientRect();
      const riak = document.createElement('span');
      const ukuran = Math.max(rect.width, rect.height);
      riak.className = 'riak';
      riak.style.width = riak.style.height = ukuran + 'px';
      riak.style.left = (event.clientX - ukuran / 2) + 'px';
      riak.style.top = (event.clientY - ukuran / 2) + 'px';
      document.body.appendChild(riak);
      setTimeout(() => riak.remove(), 520);
    });

    const tampil = entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('tampak');
          observer.unobserve(entry.target);
        }
      });
    };
    const observer = ('IntersectionObserver' in window)
      ? new IntersectionObserver(tampil, { threshold: .08 })
      : null;

    document.querySelectorAll('main .bg-white, main section, main article').forEach(elemen => {
      elemen.classList.add('muncul-saat-scroll');
      if (observer) observer.observe(elemen);
    });

    document.querySelectorAll('details').forEach(detail => {
      detail.addEventListener('toggle', () => {
        if (detail.open) detail.classList.add('sedang-terbuka');
        else detail.classList.remove('sedang-terbuka');
      });
    });
  })();
</script>
