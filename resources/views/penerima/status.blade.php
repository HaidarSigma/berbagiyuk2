@extends('layouts.situs')
@section('judul', 'Status Verifikasi - BerbagiYuk')
@section('isi')
  @php
    $u = auth()->user();
    $info = [
      'menunggu'  => ['⏳', 'Sedang diperiksa', 'bg-yellow-100 text-yellow-800', 'Admin sedang memeriksa surat keteranganmu. Kamu sudah bisa melihat barang, tapi belum bisa mengajukan permintaan.'],
      'disetujui' => ['✅', 'Terverifikasi', 'bg-green-100 text-green-800', 'Akunmu sudah diverifikasi. Kamu bisa mengajukan permintaan barang.'],
      'ditolak'   => ['❌', 'Ditolak', 'bg-red-100 text-red-700', 'Verifikasi belum berhasil. Perbaiki sesuai alasan di bawah, lalu ajukan ulang.'],
    ][$u->status_penerima] ?? ['', '-', '', ''];
  @endphp

  <div class="max-w-md mx-auto space-y-4">
    @if (session('status'))
      <div class="bg-green-50 text-green-800 text-sm rounded-lg p-3">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm p-6 space-y-3">
      <h1 class="text-2xl font-bold text-green-800">Status Verifikasi</h1>
      <div class="text-4xl">{{ $info[0] }}</div>
      <span class="inline-block text-sm font-semibold px-3 py-1 rounded-full {{ $info[2] }}">{{ $info[1] }}</span>
      <p class="text-sm text-gray-600">{{ $info[3] }}</p>
      @if ($u->status_penerima === 'ditolak' && $u->alasan_tolak_penerima)
        <p class="text-sm text-red-600 bg-red-50 rounded-lg p-3"><b>Alasan:</b> {{ $u->alasan_tolak_penerima }}</p>
      @endif
    </div>

    @if ($u->status_penerima === 'ditolak')
      <form method="POST" action="/status-penerima/ajukan-ulang" enctype="multipart/form-data" class="bg-white rounded-2xl shadow-sm p-6 space-y-3">
        @csrf
        <h2 class="font-extrabold">Ajukan ulang verifikasi</h2>
        @foreach ($errors->all() as $e) <p class="text-sm text-red-600">{{ $e }}</p> @endforeach

        <select name="jenis_penerima" class="w-full border rounded-lg px-3 py-2 bg-white" required>
          @foreach (\App\Models\Barang::JENIS as $v => $l)
            <option value="{{ $v }}" @selected(old('jenis_penerima', $u->jenis_penerima) == $v)>{{ $l }}</option>
          @endforeach
        </select>

        <div>
          <label class="text-sm text-gray-600">Surat keterangan baru (JPG, PNG, atau PDF, maks. 2 MB)</label>
          <input type="file" name="dokumen" accept=".jpg,.jpeg,.png,.pdf" class="w-full text-sm mt-1" required>
        </div>

        <button class="w-full bg-green-800 text-white rounded-lg py-2 font-semibold hover:bg-green-900">Kirim Ulang</button>
      </form>
    @endif
  </div>
@endsection