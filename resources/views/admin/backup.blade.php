@extends('admin.layout')
@section('judul', 'Backup')
@section('isi')
  @php
    $mb    = fn ($b) => number_format($b / 1048576, 1, ',', '.').' MB';
    $waktu = fn ($iso) => $iso ? \Carbon\Carbon::parse($iso)->setTimezone(config('app.timezone'))->locale('id')->translatedFormat('d M Y, H:i') : '-';
    $sibuk = $berjalan['lengkap'] || $berjalan['verifikasi'];
    $lencana = [
      'selesai'  => ['Berhasil', 'bg-green-100 text-green-800'],
      'gagal'    => ['Gagal', 'bg-red-100 text-red-700'],
      'berjalan' => ['Berjalan…', 'bg-blue-100 text-blue-800'],
    ];
    $kartu = [
      'lengkap'    => ['simpan', 'Backup lengkap', 'Database, foto, video, surat, bukti, dan kunci dalam arsip terenkripsi AES-256.', 'Buat backup sekarang', 'Mulai backup lengkap sekarang? Prosesnya bisa beberapa menit.'],
      'verifikasi' => ['centang-lingkaran', 'Verifikasi backup', 'Membuka arsip terbaru dan mencocokkan checksum semua berkas.', 'Verifikasi sekarang', 'Verifikasi arsip backup terbaru sekarang?'],
    ];
  @endphp

  @if (! $sandiTerisi)
    <div class="mb-4 flex items-center gap-2 bg-red-50 text-red-700 text-sm rounded-lg p-3 font-semibold"><x-ikon nama="peringatan" class="w-4 h-4" /> <span>BACKUP_PASSPHRASE di .env belum diisi (minimal 24 karakter). Backup tidak bisa dibuat.</span></div>
  @elseif ($basi)
    <div class="mb-4 flex items-center gap-2 bg-red-50 text-red-700 text-sm rounded-lg p-3 font-semibold"><x-ikon nama="peringatan" class="w-4 h-4" /> <span>Belum ada backup yang berhasil dalam 26 jam terakhir. Buat backup sekarang.</span></div>
  @endif

  @if ($errors->has('jenis'))
    <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">{{ $errors->first('jenis') }}</div>
  @endif

  @if ($sibuk)
    <div class="mb-4 bg-blue-50 text-blue-800 text-sm rounded-lg p-3 flex items-center gap-3">
      <span class="inline-block w-4 h-4 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>
      Proses backup sedang berjalan. Halaman ini diperbarui otomatis saat selesai.
    </div>
  @endif

  <div class="grid md:grid-cols-2 gap-4 mb-5">
    @foreach ($kartu as $kunciKartu => [$ic, $judul, $ket, $tombol, $tanya])
      @php $s = $status[$kunciKartu] ?? null; $st = $berjalan[$kunciKartu] ? 'berjalan' : ($s['status'] ?? null); @endphp
      <div class="bg-white rounded-2xl shadow-sm p-5 space-y-3">
        <div class="flex items-center justify-between gap-2">
          <h2 class="font-extrabold flex items-center gap-2"><x-ikon :nama="$ic" class="w-4 h-4 text-green-700" /> <span>{{ $judul }}</span></h2>
          @if ($st && isset($lencana[$st]))
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $lencana[$st][1] }}">{{ $lencana[$st][0] }}</span>
          @endif
        </div>
        <p class="text-sm text-gray-600">{{ $ket }}</p>

        <div class="text-xs text-gray-500 space-y-0.5">
          <div>Terakhir berhasil: <b class="text-gray-700">{{ $waktu($s['sukses'] ?? null) }}</b></div>
          @if ($s && ($s['status'] ?? '') !== 'berjalan' && ! empty($s['pesan']))
            <div class="{{ $s['status'] === 'gagal' ? 'text-red-600' : '' }}">{{ $s['pesan'] }}</div>
          @endif
        </div>

        <form method="POST" action="/admin/backup/jalankan" data-konfirmasi="{{ $tanya }}">
          @csrf
          <input type="hidden" name="jenis" value="{{ $kunciKartu }}">
          <button class="bg-green-800 text-white rounded-lg px-4 py-2 text-sm font-semibold hover:bg-green-900 disabled:bg-gray-300" @disabled($sibuk || ! $sandiTerisi)>{{ $tombol }}</button>
        </form>
      </div>
    @endforeach
  </div>

  @foreach ([['Arsip data', $data, 'Berisi database dan semua berkas unggahan.'], ['Arsip kunci', $kunci, 'Berisi .env dan APP_KEY. Simpan terpisah dari arsip data.']] as [$judulTabel, $daftar, $ketTabel])
    <div class="bg-white rounded-2xl shadow-sm mb-5 overflow-x-auto">
      <div class="px-5 pt-4">
        <h2 class="font-extrabold">{{ $judulTabel }}</h2>
        <p class="text-xs text-gray-500">{{ $ketTabel }}</p>
      </div>
      <table class="w-full text-sm mt-2">
        <thead class="text-left text-xs text-gray-500 border-b">
          <tr><th class="p-3 pl-5">Berkas</th><th class="p-3">Dibuat</th><th class="p-3">Ukuran</th><th class="p-3 pr-5">Unduh (butuh PIN)</th></tr>
        </thead>
        <tbody>
          @forelse ($daftar as $b)
            <tr class="border-b last:border-0">
              <td class="p-3 pl-5 font-mono text-xs">{{ $b['nama'] }}</td>
              <td class="p-3 whitespace-nowrap">{{ $b['waktu']->locale('id')->translatedFormat('d M Y, H:i') }}</td>
              <td class="p-3 whitespace-nowrap">{{ $mb($b['ukuran']) }}</td>
              <td class="p-3 pr-5">
                <form method="POST" action="/admin/backup/unduh" class="flex gap-1">
                  @csrf
                  <input type="hidden" name="berkas" value="{{ $b['nama'] }}">
                  <input type="password" name="pin" inputmode="numeric" maxlength="6" placeholder="PIN" class="w-20 border rounded-lg px-2 py-1 text-xs" required>
                  <button class="inline-flex items-center gap-1 bg-gray-800 text-white rounded-lg px-3 py-1 text-xs"><x-ikon nama="unduh" class="w-3.5 h-3.5" /> Unduh</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="4" class="p-4 pl-5 text-gray-500">Belum ada arsip.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  @endforeach

  @if ($errors->has('pin'))
    <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">{{ $errors->first('pin') }}</div>
  @endif

  <div class="bg-white rounded-2xl shadow-sm p-5 text-sm text-gray-600 space-y-1.5">
    <h2 class="font-extrabold text-gray-800">Pengaturan</h2>
    <div>Menyimpan <b>{{ $simpan }}</b> arsip data terakhir.</div>
    <div class="flex flex-wrap items-center gap-1">
      <x-ikon nama="lokasi" class="w-4 h-4 text-gray-400" />
      <span>Salinan luar lokasi:</span>
      @if (! $salinanDiatur)
        <span class="text-yellow-700 font-semibold">belum diatur</span> (isi BACKUP_SALINAN_DIR di .env, misalnya folder di flashdisk atau disk lain).
      @elseif ($salinanTerbaru && $data && $salinanTerbaru === $data[0]['nama'])
        <span class="inline-flex items-center gap-1 text-green-700 font-semibold">sinkron <x-ikon nama="cek" class="w-3.5 h-3.5" /></span> ({{ $salinanTerbaru }})
      @else
        <span class="text-red-600 font-semibold">belum sinkron</span> dengan arsip terbaru. Periksa apakah media salinan terpasang.
      @endif
    </div>
    <div class="flex flex-wrap items-center gap-1">
      <x-ikon nama="kunci" class="w-4 h-4 text-gray-400" />
      <span>Kata sandi enkripsi:</span>
      @if ($sandiTerisi)
        <span class="inline-flex items-center gap-1 text-green-700 font-semibold">terisi <x-ikon nama="cek" class="w-3.5 h-3.5" /></span>
      @else
        <span class="text-red-600 font-semibold">belum diisi</span>
      @endif
      <span>(disimpan di .env dan harus dicatat di luar laptop).</span>
    </div>
    <div class="pt-1 text-xs text-gray-500">Pemulihan sengaja tidak tersedia di halaman ini karena menimpa data. Ikuti prosedur pemulihan di dokumen kebijakan (Prosedur 15.4).</div>
  </div>

  @if ($sibuk)
    <script nonce="{{ $cspNonce ?? '' }}">
      const t = setInterval(async () => {
        try {
          const r = await fetch('/admin/backup/status', { headers: { 'Accept': 'application/json' } });
          const d = await r.json();
          if (!d.berjalan) { clearInterval(t); location.reload(); }
        } catch (e) {}
      }, 3000);
    </script>
  @endif
@endsection