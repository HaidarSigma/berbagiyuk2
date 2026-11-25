document.documentElement.classList.add('siap');

if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
  document.querySelectorAll('[data-placeholder-animasi]').forEach(input => {
    const saran = input.dataset.placeholderAnimasi.split('|').filter(Boolean);
    const bawaan = input.dataset.placeholderAsli || input.placeholder;
    if (!saran.length) return;

    let indeks = 0;
    let posisi = 0;
    let timer;

    const hapusHuruf = () => {
      if (input.value || document.activeElement === input) return;
      posisi -= 1;
      input.placeholder = saran[indeks].slice(0, posisi);
      if (posisi > 0) {
        timer = setTimeout(hapusHuruf, 35);
      } else {
        indeks = (indeks + 1) % saran.length;
        timer = setTimeout(tulisHuruf, 250);
      }
    };

    const tulisHuruf = () => {
      if (input.value || document.activeElement === input) return;
      const teks = saran[indeks];
      posisi += 1;
      input.placeholder = teks.slice(0, posisi);
      if (posisi < teks.length) {
        timer = setTimeout(tulisHuruf, 70);
      } else {
        timer = setTimeout(hapusHuruf, 1300);
      }
    };

    input.addEventListener('focus', () => {
      clearTimeout(timer);
      input.placeholder = bawaan;
    });

    input.addEventListener('blur', () => {
      if (!input.value) tulisHuruf();
    });

    tulisHuruf();
  });
}
