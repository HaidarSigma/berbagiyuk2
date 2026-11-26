@extends('petugas.layout')
@section('judul', 'Pengambilan')
@section('isi')
  @php
    $wa   = fn ($no) => 'https://wa.me/'.preg_replace('/^0/', '62', preg_replace('/\D/', '', (string) $no));
    $peta = fn ($lat, $lng) => $lat ? 'https://www.google.com/maps?q='.$lat.','.$lng : null;
  @endphp

  <h2 class="font-extrabold mb-2">Menunggu diambil <span class="text-sm font-normal text-gray-500">(tugasku)</span></h2>

  @forelse ($kelompok as $grup)
    @php $b0 = $grup->first()->barang; $d = $b0->user; @endphp
    <div class="bg-white rounded-2xl shadow-sm p-4 mb-4 space-y-3">
      <div class="text-sm space-y-1">
        <div class="text-xs font-semibold text-gray-500">AMBIL DARI DONATUR @if ($b0->donasi_id) · DONASI #{{ $b0->donasi_id }} @endif</div>
        @if ($b0->donasi?->kebutuhanMendesak)
          <div class="rounded-lg bg-orange-50 px-3 py-2 text-xs font-semibold text-orange-900">Kampanye khusus: {{ $b0->donasi->kebutuhanMendesak->judul }} — {{ $b0->donasi->kebutuhanMendesak->lokasi }}</div>
        @endif
        <div class="font-semibold">{{ $d->name }}</div>
        <div class="text-gray-600">{{ $d->alamat }}</div>
        <div class="text-xs text-gray-500">Area: {{ $b0->lokasi }}</div>
        <div class="flex flex-wrap gap-3 pt-1 font-semibold text-blue-700">
          @if ($peta($b0->latitude, $b0->longitude))<a target="_blank" href="{{ $peta($b0->latitude, $b0->longitude) }}" class="inline-flex items-center gap-1"><x-ikon nama="peta" class="w-4 h-4" /> Peta</a>@endif
          <a href="tel:{{ $d->no_hp }}" class="inline-flex items-center gap-1"><x-ikon nama="telepon" class="w-4 h-4" /> Telepon</a>
          <a target="_blank" href="{{ $wa($d->no_hp) }}" class="inline-flex items-center gap-1"><x-ikon nama="pesan" class="w-4 h-4" /> WhatsApp</a>
        </div>
      </div>

      <form method="POST" action="/petugas/pengambilan/selesai" enctype="multipart/form-data" data-memuat="Menyimpan..." class="form-ambil space-y-3">
        @csrf
        <div class="text-sm font-semibold">Barang yang diambil (hilangkan centang kalau tidak jadi)</div>
        @foreach ($grup as $p)
          @php $b = $p->barang; @endphp
          <label class="flex items-start gap-2 border rounded-lg p-2 text-sm">
            <input type="checkbox" name="pengambilan_id[]" value="{{ $p->id }}" checked class="mt-1">
            <span>
              <b>{{ $b->judul }}</b> <span class="text-xs text-gray-500">· {{ $b->kategori->nama }} · {{ $b->labelKondisi() }}@if ($b->jumlahLabel()) · {{ $b->jumlahLabel() }}@endif</span>
              @if ($b->isMakanan())
                <span class="block text-[11px] text-amber-800">Batas konsumsi: {{ $b->tanggal_kedaluwarsa ? $b->tanggal_kedaluwarsa->format('d/m/Y') : 'Belum diisi' }} · Penyimpanan: {{ $b->cara_penyimpanan ?: 'Belum diisi' }}</span>
              @endif
              <span class="block text-[11px] text-gray-500">Label berakhiran <b class="font-mono">{{ substr($b->token, -4) }}</b></span>
            </span>
          </label>
        @endforeach

        <div class="rounded-xl bg-slate-50 border p-3 space-y-2">
          <div class="text-sm font-semibold">Pindai label QR pada barang <span class="text-xs font-normal text-gray-500">(<span class="hitung">0</span> label dipindai)</span></div>
          <p class="text-xs text-gray-500">Arahkan QR ke tengah kotak kamera. Pastikan cukup terang dan QR terlihat jelas.</p>

          <div class="flex flex-wrap gap-2">
            <button type="button" class="btn-kamera inline-flex items-center gap-1.5 bg-gray-800 text-white rounded-lg px-3 py-2 text-sm"><x-ikon nama="kamera" class="w-4 h-4" /> Kamera</button>
            <label class="inline-flex items-center gap-1.5 bg-gray-800 text-white rounded-lg px-3 py-2 text-sm cursor-pointer">
              <x-ikon nama="gambar" class="w-4 h-4" /> Foto label
              <input type="file" accept="image/*" capture="environment" class="input-foto-label hidden">
            </label>
          </div>

          <div class="area-kamera hidden space-y-2">
            <div id="reader-{{ $loop->index }}" class="rounded-lg overflow-hidden"></div>
            <button type="button" class="btn-stop text-xs text-red-600">Tutup kamera</button>
          </div>

          <div class="flex gap-2">
            <input class="input-manual flex-1 border rounded-lg px-3 py-2 text-xs font-mono uppercase" placeholder="Atau ketik ID: BY-...">
            <button type="button" class="btn-manual bg-gray-200 rounded-lg px-3 text-sm">Tambah</button>
          </div>

          <p class="pesan-scan text-xs text-red-600"></p>
          <div class="daftar-token flex flex-wrap gap-2"></div>
        </div>

        <label class="text-sm font-semibold block">Foto bukti pengambilan</label>
        <input type="file" name="foto" accept="image/*" capture="environment" class="w-full text-sm" required>
        <button class="w-full bg-blue-700 text-white rounded-xl py-2.5 font-semibold hover:bg-blue-800">Tandai Sudah Diambil</button>
      </form>

      <div class="flex flex-wrap gap-3 text-xs">
        @foreach ($grup as $p)
          <form method="POST" action="/petugas/pengambilan/{{ $p->id }}/lepas" data-konfirmasi="Lepas tugas ini?">@csrf
            <button class="text-red-600">Lepas: {{ \Illuminate\Support\Str::limit($p->barang->judul, 22) }}</button>
          </form>
        @endforeach
      </div>
    </div>
  @empty
    <p class="text-sm text-gray-500 mb-4">Belum ada tugas yang menunggu diambil. Ambil dari daftar di bawah.</p>
  @endforelse

  <h2 class="font-extrabold mb-2 mt-6">Tersedia untuk diambil</h2>
  <div class="space-y-3">
    @forelse ($tersedia as $grup)
      @php $b0 = $grup->first()->barang; @endphp
      <div class="bg-white rounded-2xl shadow-sm p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="space-y-1">
          <div class="font-bold">
            {{ \Illuminate\Support\Str::before($b0->user->name, ' ') }}
            <span class="text-xs font-normal text-gray-500">· {{ $b0->lokasi }}</span>
            @if ($b0->donasi_id)<span class="text-[11px] bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Donasi #{{ $b0->donasi_id }}</span>@endif
          </div>
          <ul class="text-sm text-gray-700 list-disc ml-5">
            @foreach ($grup as $p)
              <li>{{ $p->barang->judul }} <span class="text-xs text-gray-500">· {{ $p->barang->kategori->nama }} · {{ $p->barang->labelKondisi() }}@if ($p->barang->jumlahLabel()) · {{ $p->barang->jumlahLabel() }}@endif</span>
                @if ($p->barang->isMakanan())
                  <span class="block text-[11px] text-amber-800">Batas konsumsi: {{ $p->barang->tanggal_kedaluwarsa ? $p->barang->tanggal_kedaluwarsa->format('d/m/Y') : 'Belum diisi' }} · Penyimpanan: {{ $p->barang->cara_penyimpanan ?: 'Belum diisi' }}</span>
                @endif
              </li>
            @endforeach
          </ul>
        </div>
        <form method="POST" action="/petugas/pengambilan/ambil">@csrf
          @foreach ($grup as $p)<input type="hidden" name="id[]" value="{{ $p->id }}">@endforeach
          <button class="w-full sm:w-auto bg-blue-700 text-white rounded-xl px-5 py-2.5 text-sm font-semibold hover:bg-blue-800">Ambil {{ $grup->count() }} barang</button>
        </form>
      </div>
    @empty
      <p class="text-sm text-gray-500">Belum ada barang yang perlu diambil. Tugas muncul setelah admin menyetujui barang donatur.</p>
    @endforelse
  </div>

  <div id="reader-berkas" class="hidden"></div>

  <script src="{{ asset("vendor/html5-qrcode/html5-qrcode.min.js") }}"></script>
  <script nonce="{{ $cspNonce ?? '' }}">
    const POLA = /^BY-[A-Z0-9]{24}$/;
    let aktif = null;

    const pesan  = (form, teks) => form.querySelector('.pesan-scan').textContent = teks || '';
    const hitung = form => form.querySelector('.hitung').textContent = form.querySelectorAll('input[name="token_barang[]"]').length;

    function tambahToken(form, teks) {
      const t = (teks || '').trim().toUpperCase();
      if (!POLA.test(t)) { pesan(form, 'Itu bukan label BerbagiYuk.'); return false; }
      if (form.querySelector('input[name="token_barang[]"][value="' + t + '"]')) { pesan(form, 'Label ini sudah dipindai.'); return false; }

      const h = document.createElement('input');
      h.type = 'hidden'; h.name = 'token_barang[]'; h.value = t;
      form.appendChild(h);

      const chip = document.createElement('span');
      chip.className = 'inline-flex items-center gap-1 bg-green-100 text-green-800 rounded-full px-2 py-0.5 text-xs font-mono';
      chip.innerHTML = 'OK …' + t.slice(-4) + ' <button type="button" class="text-green-900 font-sans font-bold">×</button>';
      chip.querySelector('button').onclick = () => { h.remove(); chip.remove(); hitung(form); };
      form.querySelector('.daftar-token').appendChild(chip);

      pesan(form, '');
      hitung(form);
      if (navigator.vibrate) navigator.vibrate(80);
      return true;
    }

    async function hentikan() {
      if (!aktif) return;
      try { await aktif.inst.stop(); aktif.inst.clear(); } catch (e) {}
      aktif.area.classList.add('hidden');
      aktif = null;
    }

    async function mulaiKamera(form, idx) {
      if (!window.isSecureContext) {
        pesan(form, 'Kamera langsung butuh HTTPS. Pakai tombol "Foto label" atau ketik ID.');
        return;
      }
      await hentikan();

      const area = form.querySelector('.area-kamera');
      area.classList.remove('hidden');
      const inst = new Html5Qrcode('reader-' + idx);
      aktif = { inst, area };

      try {
        const kameraList = await Html5Qrcode.getCameras();
        if (!kameraList.length) throw new Error('Kamera tidak ditemukan.');
        const kamera = kameraList.find(k => /back|rear|environment|belakang/i.test(k.label)) || kameraList[0];
        await inst.start(kamera.id, {
          fps: 10,
          qrbox: (lebar, tinggi) => {
            const ukuran = Math.min(320, Math.floor(Math.min(lebar, tinggi) * 0.85));
            return { width: ukuran, height: ukuran };
          },
        }, teks => tambahToken(form, teks));
      } catch (e) {
        area.classList.add('hidden');
        aktif = null;
        const alasan = {
          NotAllowedError: 'Izin kamera ditolak. Izinkan akses kamera untuk situs ini di browser.',
          NotFoundError: 'Kamera tidak ditemukan.',
          NotReadableError: 'Kamera sedang dipakai aplikasi lain.',
          OverconstrainedError: 'Kamera tidak mendukung mode yang diminta.',
        }[e?.name] || e?.message || 'Periksa izin akses dan koneksi kamera.';
        pesan(form, `${alasan} Pakai "Foto label" atau ketik ID.`);
      }
    }

    async function dariFoto(form, berkas) {
      if (!berkas) return;
      const inst = new Html5Qrcode('reader-berkas');
      try {
        tambahToken(form, await inst.scanFile(berkas, false));
      } catch (e) {
        pesan(form, 'QR tidak terbaca. Pastikan foto jelas dan seluruh QR terlihat.');
      } finally {
        try { inst.clear(); } catch (e) {}
      }
    }

    document.querySelectorAll('.form-ambil').forEach((form, idx) => {
      const manual = form.querySelector('.input-manual');

      form.querySelector('.btn-kamera').onclick = () => mulaiKamera(form, idx);
      form.querySelector('.btn-stop').onclick = hentikan;
      form.querySelector('.input-foto-label').onchange = e => { dariFoto(form, e.target.files[0]); e.target.value = ''; };
      form.querySelector('.btn-manual').onclick = () => { if (tambahToken(form, manual.value)) manual.value = ''; };
      manual.onkeydown = e => { if (e.key === 'Enter') { e.preventDefault(); form.querySelector('.btn-manual').click(); } };
      form.addEventListener('submit', hentikan);
    });
  </script>
@endsection