// Dimuat di <head> sebelum halaman digambar, supaya tidak berkedip putih pada tema gelap
(function () {
  var sistem = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)');

  var pakai = function () {
    var s = null;
    try { s = localStorage.getItem('tema'); } catch (e) {}
    var gelap = s ? s === 'gelap' : !!(sistem && sistem.matches);
    document.documentElement.classList.toggle('dark', gelap);
  };

  pakai();
  // Ikut perangkat selama pengguna belum memilih sendiri
  if (sistem && sistem.addEventListener) sistem.addEventListener('change', pakai);
})();