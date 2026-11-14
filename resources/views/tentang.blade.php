@extends('layouts.situs')
@section('judul', 'Tentang Kami - BerbagiYuk')
@section('isi')
  @php
    // Ganti dengan kontak organisasi yang sebenarnya
    $kontak = ['email' => 'kontak@berbagiyuk.test', 'telepon' => '0271-000000', 'alamat' => config('wilayah.nama').', Jawa Tengah'];

    $langkah = [
      ['🎁', 'Donatur membagikan', 'Donatur mengirim barang beserta foto dan titik pengambilan.'],
      ['✅', 'Admin memeriksa', 'Setiap barang diperiksa terhadap Aturan Barang sebelum tampil.'],
      ['🔍', 'Penerima mengajukan', 'Penerima terverifikasi mencari barang dan menulis alasan kebutuhannya.'],
      ['🤝', 'Admin menyetujui', 'Admin memilih permintaan yang paling membutuhkan.'],
      ['🚚', 'Petugas mengantar', 'Petugas mengambil dari donatur lalu mengantar ke penerima.'],
    ];
    $peran = [
      ['Donatur', 'Membagikan barang layak pakai yang sudah tidak terpakai.'],
      ['Penerima', 'Individu, keluarga, panti, atau korban bencana yang sudah diverifikasi.'],
      ['Petugas', 'Mengambil dan mengantar barang, dibuktikan dengan foto dan kode serah terima.'],
      ['Admin', 'Memverifikasi barang, penerima, dan permintaan, serta menjaga keamanan sistem.'],
    ];
    $komitmen = [
      'Setiap barang diperiksa admin sebelum tampil.',
      'Penerima diverifikasi lewat surat keterangan, yang disimpan terenkripsi dan hanya dilihat admin.',
      'Alamat dan nomor HP tidak tampil di halaman publik. Petugas baru melihatnya setelah mengambil tugas.',
      'Akun dilindungi verifikasi email, password kuat, dan PIN.',
      'Serah terima dikonfirmasi dengan kode dari penerima dan foto bukti.',
      'Aktivitas penting tercatat dan bisa diaudit.',
    ];
    $faq = [
      ['Apakah BerbagiYuk gratis?', 'Ya. Barang dibagikan tanpa biaya dan tidak boleh diperjualbelikan.'],
      ['Barang apa saja yang boleh dibagikan?', 'Barang layak pakai sesuai Aturan Barang. Lihat halaman Aturan Barang untuk daftar lengkapnya.'],
      ['Bagaimana menjadi penerima?', 'Daftar sebagai penerima, pilih jenis penerima, dan unggah surat keterangan dari RT, kelurahan, atau lembaga. Admin akan memeriksanya.'],
      ['Barang saya ditolak, apa yang harus dilakukan?', 'Buka Barang Saya, baca alasan penolakannya, perbaiki, lalu ajukan ulang.'],
      ['Apakah data pribadi saya aman?', 'Nomor HP, alamat, dan surat keterangan disimpan terenkripsi. Alamat tidak pernah tampil di halaman publik.'],
    ];
  @endphp

  <div class="space-y-10">
    <section class="rounded-3xl bg-gradient-to-br from-green-100 via-green-50 to-white p-8 md:p-12">
      <span class="inline-block bg-white text-green-800 text-xs font-semibold px-3 py-1 rounded-full shadow-sm">🌱 Tentang BerbagiYuk</span>
      <h1 class="mt-4 text-3xl md:text-4xl font-extrabold text-gray-900">Berbagi Sedikit, <span class="text-green-700">Berarti Banyak.</span></h1>
      <p class="mt-4 text-gray-600 max-w-2xl">BerbagiYuk mempertemukan orang yang punya barang layak pakai dengan orang yang membutuhkannya di area {{ config('wilayah.nama') }}. Pengambilan dan pengantaran dibantu petugas, dan setiap tahapnya diperiksa supaya barang sampai ke yang berhak.</p>
    </section>

    <section>
      <h2 class="text-xl font-extrabold mb-4">Bagaimana cara kerjanya?</h2>
      <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3">
        @foreach ($langkah as $i => [$ic, $judul, $ket])
          <div class="bg-white rounded-2xl shadow-sm p-4">
            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-xl">{{ $ic }}</div>
            <div class="text-xs text-gray-400 mt-3">Langkah {{ $i + 1 }}</div>
            <div class="font-bold text-sm">{{ $judul }}</div>
            <p class="text-xs text-gray-500 mt-1">{{ $ket }}</p>
          </div>
        @endforeach
      </div>
    </section>

    <section class="grid lg:grid-cols-2 gap-6">
      <div>
        <h2 class="text-xl font-extrabold mb-4">Siapa saja yang terlibat?</h2>
        <div class="space-y-2">
          @foreach ($peran as [$nama, $ket])
            <div class="bg-white rounded-xl shadow-sm p-4 text-sm"><b>{{ $nama }}</b><p class="text-gray-500 text-xs mt-0.5">{{ $ket }}</p></div>
          @endforeach
        </div>
      </div>
      <div>
        <h2 class="text-xl font-extrabold mb-4">Komitmen keamanan dan privasi</h2>
        <div class="bg-green-50 rounded-2xl p-5">
          <ul class="space-y-2 text-sm text-gray-700">
            @foreach ($komitmen as $k)<li class="flex gap-2"><span class="text-green-700">✔</span><span>{{ $k }}</span></li>@endforeach
          </ul>
        </div>
      </div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm p-6">
      <h2 class="text-xl font-extrabold">Wilayah layanan</h2>
      <p class="text-sm text-gray-600 mt-2">Saat ini BerbagiYuk melayani area<b>{{ config('wilayah.nama') }}</b>. Pengambilan dan pengantaran barang dilakukan di dalam wilayah ini.</p>
    </section>

    <section>
      <h2 class="text-xl font-extrabold mb-4">Pertanyaan yang sering diajukan</h2>
      <div class="space-y-2">
        @foreach ($faq as [$tanya, $jawab])
          <details class="bg-white rounded-xl shadow-sm px-5 py-3">
            <summary class="list-none cursor-pointer font-semibold text-sm [&::-webkit-details-marker]:hidden">{{ $tanya }}</summary>
            <p class="text-sm text-gray-600 mt-2">{{ $jawab }}</p>
          </details>
        @endforeach
      </div>
    </section>

    <section class="bg-green-900 text-green-100 rounded-2xl p-6 text-sm space-y-1">
      <h2 class="text-lg font-extrabold text-white mb-2">Hubungi kami</h2>
      <div>✉️ {{ $kontak['email'] }}</div>
      <div>📞 {{ $kontak['telepon'] }}</div>
      <div>📍 {{ $kontak['alamat'] }}</div>
    </section>
  </div>
@endsection