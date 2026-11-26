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

const aturKondisiBarang = kategori => {
  const form = kategori.closest('[data-form-barang]');
  if (!form) return;

  const kondisi = form.querySelector('[data-kondisi-barang]');
  const infoMakanan = form.querySelector('[data-info-makanan]');
  if (!kondisi || !infoMakanan) return;

  const pilihan = kategori.options[kategori.selectedIndex];
  const makanan = pilihan && pilihan.dataset.makanan === '1';

  kondisi.querySelectorAll('option[data-label-umum]').forEach(option => {
    const perluPerbaikan = option.value === 'Perlu Perbaikan';
    option.textContent = makanan ? option.dataset.labelMakanan : option.dataset.labelUmum;
    option.hidden = makanan && perluPerbaikan;
    option.disabled = makanan && perluPerbaikan;
  });

  if (makanan && kondisi.value === 'Perlu Perbaikan') kondisi.value = '';

  infoMakanan.classList.toggle('hidden', !makanan);
  infoMakanan.querySelectorAll('[data-info-makanan-input]').forEach(input => {
    input.disabled = !makanan;
    input.required = makanan;
  });
};

document.querySelectorAll('[data-kategori-barang]').forEach(aturKondisiBarang);
document.addEventListener('change', e => {
  if (e.target.matches('[data-kategori-barang]')) aturKondisiBarang(e.target);
});

const pilihanKampanye = document.querySelector('[data-kebutuhan-campaign]');
if (pilihanKampanye) {
  const penerimaUmum = document.querySelector('[data-syarat-penerima-umum]');
  const penerimaKorban = document.querySelector('[data-syarat-penerima-korban]');
  const inputKorban = document.querySelector('[data-syarat-korban-input]');
  const infoKampanye = document.querySelector('[data-kebutuhan-campaign-info]');

  const aturKampanye = () => {
    const kampanyeDipilih = Boolean(pilihanKampanye.value);
    const kampanye = pilihanKampanye.options[pilihanKampanye.selectedIndex];
    if (penerimaUmum) {
      penerimaUmum.classList.toggle('hidden', kampanyeDipilih);
      penerimaUmum.querySelectorAll('input').forEach(input => { input.disabled = kampanyeDipilih; });
    }
    if (penerimaKorban) penerimaKorban.classList.toggle('hidden', !kampanyeDipilih);
    if (inputKorban) inputKorban.disabled = !kampanyeDipilih;
    if (infoKampanye) {
      infoKampanye.textContent = kampanyeDipilih
        ? `Donasi ditujukan untuk korban bencana. Kebutuhan utama: ${kampanye.dataset.keterangan || 'lihat rincian pada halaman Kebutuhan Mendesak'}.`
        : '';
      infoKampanye.classList.toggle('hidden', !kampanyeDipilih);
    }
  };

  pilihanKampanye.addEventListener('change', aturKampanye);
  aturKampanye();
}

// Konfirmasi sebelum kirim form: <form data-konfirmasi="Hapus?">
document.addEventListener('submit', e => {
  const k = e.target.dataset && e.target.dataset.konfirmasi;
  if (k && !confirm(k)) e.preventDefault();
}, true);

// Cegah kirim ganda dan tampilkan keadaan memuat: <form data-memuat="Mengunggah...">
document.addEventListener('submit', e => {
  const f = e.target;
  if (e.defaultPrevented || !f.dataset || !f.dataset.memuat) return;
  if (f.dataset.terkirim) { e.preventDefault(); return; }

  f.dataset.terkirim = '1';
  const tombol = f.querySelector('button:not([type]), button[type="submit"]');
  if (tombol) {
    tombol.disabled = true;
    const putar = document.createElement('span');
    putar.className = 'spinner-kecil';
    tombol.textContent = '';
    tombol.append(putar, ' ' + f.dataset.memuat);
  }
});

// Halaman dipulihkan dari cache tombol Kembali: muat ulang supaya tombol tidak macet
window.addEventListener('pageshow', e => { if (e.persisted) location.reload(); });

document.addEventListener('click', e => {
  const el = e.target.closest('[data-salin],[data-cetak],[data-kembali],[data-tutup],[data-galeri],[data-galeri-slide],[data-galeri-arah]');
  if (!el) return;

  if (el.matches('[data-galeri-slide],[data-galeri-arah]')) {
    const root = el.closest('[data-galeri-root]');
    const utama = root && root.querySelector('[data-galeri-utama]');
    const tombolFoto = root ? [...root.querySelectorAll('[data-galeri-slide]')] : [];
    if (!utama || !tombolFoto.length) return;

    const indeksAktif = Number(utama.dataset.indeks || 0);
    const indeksBaru = el.hasAttribute('data-galeri-arah')
      ? (indeksAktif + Number(el.dataset.galeriArah) + tombolFoto.length) % tombolFoto.length
      : Number(el.dataset.galeriSlide);
    const tombolBaru = tombolFoto[indeksBaru];
    const fotoBaru = tombolBaru && tombolBaru.querySelector('img');
    if (!fotoBaru) return;

    utama.src = fotoBaru.currentSrc || fotoBaru.src;
    utama.dataset.indeks = String(indeksBaru);
    utama.alt = fotoBaru.alt || utama.alt;

    const tautanFoto = root.querySelector('[data-galeri-link]');
    if (tautanFoto) tautanFoto.href = utama.src;

    tombolFoto.forEach((tombol, indeks) => {
      const aktif = indeks === indeksBaru;
      tombol.setAttribute('aria-pressed', String(aktif));
      tombol.classList.toggle('border-green-700', aktif);
      tombol.classList.toggle('border-transparent', !aktif);
    });
  } else if (el.dataset.salin !== undefined) {
    navigator.clipboard.writeText(el.dataset.salin);
    el.textContent = '✔ Tersalin';
  } else if (el.hasAttribute('data-cetak')) {
    window.print();
  } else if (el.hasAttribute('data-kembali')) {
    e.preventDefault();
    history.back();
  } else if (el.hasAttribute('data-tutup')) {
    const wadah = el.parentElement;
    if (!wadah) return;
    wadah.classList.add('opacity-0', '-translate-y-2');
    setTimeout(() => wadah.remove(), 500);
  } else if (el.dataset.galeri) {
    const utama = document.getElementById(el.dataset.galeri);
    if (utama) utama.src = el.currentSrc || el.src;
  }
});

const notifikasiLink = document.querySelector('[data-notifikasi-poll]');
if (notifikasiLink) {
  const lencana = notifikasiLink.querySelector('[data-notifikasi-badge]');
  const toastNotifikasi = document.querySelector('[data-notifikasi-toast]');
  let jumlahNotifikasi = Number(notifikasiLink.dataset.jumlah || 0);
  let idNotifikasiTerbaru = notifikasiLink.dataset.terbaru || '';

  const tampilkanNotifikasi = notifikasi => {
    if (!toastNotifikasi) return;

    const link = toastNotifikasi.querySelector('[data-notifikasi-toast-link]');
    const judul = toastNotifikasi.querySelector('[data-notifikasi-toast-judul]');
    const pesan = toastNotifikasi.querySelector('[data-notifikasi-toast-pesan]');
    if (!link || !judul || !pesan) return;

    judul.textContent = notifikasi.judul;
    pesan.textContent = notifikasi.pesan;
    link.href = typeof notifikasi.url === 'string'
      && notifikasi.url.startsWith('/')
      && !notifikasi.url.startsWith('//')
      ? notifikasi.url
      : '/notifikasi';
    toastNotifikasi.classList.remove('hidden');
    clearTimeout(toastNotifikasi.hideTimer);
    toastNotifikasi.hideTimer = setTimeout(() => toastNotifikasi.classList.add('hidden'), 8000);
  };

  const perbaruiNotifikasi = async () => {
    if (document.hidden) return;

    try {
      const response = await fetch(notifikasiLink.dataset.url, {
        cache: 'no-store',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
      });
      if (!response.ok) throw new Error(`Notifikasi gagal dimuat (${response.status}).`);

      const data = await response.json();
      const jumlahBaru = Number(data.jumlah) || 0;
      const terbaru = data.terbaru;

      if (terbaru && terbaru.id !== idNotifikasiTerbaru) tampilkanNotifikasi(terbaru);
      idNotifikasiTerbaru = terbaru ? terbaru.id : '';
      jumlahNotifikasi = jumlahBaru;
      if (lencana) {
        lencana.textContent = String(jumlahNotifikasi);
        lencana.classList.toggle('hidden', jumlahNotifikasi === 0);
      }
      notifikasiLink.setAttribute('aria-label', `Notifikasi, ${jumlahNotifikasi} belum dibaca`);
    } catch (error) {
      console.error('Gagal memperbarui notifikasi.', error);
    }
  };

  const tombolTutupNotifikasi = toastNotifikasi && toastNotifikasi.querySelector('[data-notifikasi-tutup]');
  if (tombolTutupNotifikasi) {
    tombolTutupNotifikasi.addEventListener('click', () => toastNotifikasi.classList.add('hidden'));
  }

  notifikasiLink.setAttribute('aria-label', `Notifikasi, ${jumlahNotifikasi} belum dibaca`);
  window.setInterval(perbaruiNotifikasi, 30000);
}
  
// Ganti tema terang/gelap: <button data-tema>
document.addEventListener('click', e => {
  if (!e.target.closest('[data-tema]')) return;

  const gelap = !document.documentElement.classList.contains('dark');
  document.documentElement.classList.toggle('dark', gelap);
  try { localStorage.setItem('tema', gelap ? 'gelap' : 'terang'); } catch (x) {}
});