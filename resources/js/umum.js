// Konfirmasi sebelum kirim form: <form data-konfirmasi="Hapus?">
document.addEventListener('submit', e => {
  const k = e.target.dataset && e.target.dataset.konfirmasi;
  if (k && !confirm(k)) e.preventDefault();
}, true);

document.addEventListener('click', e => {
  const el = e.target.closest('[data-salin],[data-cetak],[data-kembali],[data-tutup],[data-galeri]');
  if (!el) return;

  if (el.dataset.salin !== undefined) {
    navigator.clipboard.writeText(el.dataset.salin);
    el.textContent = '✔ Tersalin';
  } else if (el.hasAttribute('data-cetak')) {
    window.print();
  } else if (el.hasAttribute('data-kembali')) {
    e.preventDefault();
    history.back();
  } else if (el.hasAttribute('data-tutup')) {
    el.parentElement.remove();
  } else if (el.dataset.galeri) {
    document.getElementById(el.dataset.galeri).src = el.src;
  }
});