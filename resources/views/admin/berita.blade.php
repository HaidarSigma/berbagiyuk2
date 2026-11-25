@extends('admin.layout')
@section('judul', 'Berita')
@section('isi')
  @if ($errors->any())
    <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">@foreach (array_unique($errors->all()) as $e)<p>{{ $e }}</p>@endforeach</div>
  @endif

  <details class="bg-white rounded-2xl shadow-sm mb-5" @if ($errors->any()) open @endif>
    <summary class="list-none cursor-pointer px-5 py-3 font-semibold text-green-800">Tambah berita</summary>
    <form method="POST" action="/admin/berita" enctype="multipart/form-data" class="p-5 pt-2 grid md:grid-cols-2 gap-3">
      @csrf
      <input name="judul" value="{{ old('judul') }}" placeholder="Judul berita" class="md:col-span-2 border rounded-lg px-3 py-2 text-sm" required>
      <input name="ringkasan" value="{{ old('ringkasan') }}" placeholder="Ringkasan singkat (tampil di slider)" class="md:col-span-2 border rounded-lg px-3 py-2 text-sm" required>
      <textarea name="isi" rows="5" placeholder="Isi berita lengkap (opsional)" class="md:col-span-2 border rounded-lg px-3 py-2 text-sm">{{ old('isi') }}</textarea>
      <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="border rounded-lg px-3 py-2 text-sm" required>
      <div>
        <input type="file" name="foto" accept="image/*" class="w-full text-sm">
        <p class="text-[11px] text-gray-500">Foto opsional, maks. 2 MB. Rasio 16:9 paling pas.</p>
      </div>
      <button class="md:col-span-2 bg-green-800 text-white rounded-lg py-2 text-sm font-semibold hover:bg-green-900">Tambahkan</button>
    </form>
  </details>

  <div class="space-y-3">
    @forelse ($daftar as $b)
      <div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 {{ $b->terbit ? '' : 'opacity-60' }}">
        <div class="w-28 h-20 shrink-0 rounded-xl bg-green-100 overflow-hidden flex items-center justify-center text-3xl">
          @if ($b->foto)<img src="{{ asset('storage/'.$b->foto) }}" class="w-full h-full object-cover" alt="">@else 📰 @endif
        </div>

        <div class="flex-1 min-w-0 space-y-1">
          <div class="flex flex-wrap items-center gap-2">
            <div class="font-bold">{{ $b->judul }}</div>
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $b->terbit ? 'bg-green-100 text-green-800' : 'bg-gray-200 text-gray-600' }}">{{ $b->terbit ? 'Tampil' : 'Disembunyikan' }}</span>
          </div>
          <p class="text-sm text-gray-600">{{ $b->ringkasan }}</p>
          <div class="text-xs text-gray-500">{{ $b->tanggal->locale('id')->translatedFormat('d F Y') }}</div>

          <div class="flex flex-wrap gap-4 pt-1 text-sm">
            <a href="/berita/{{ $b->id }}" target="_blank" class="text-green-800 font-semibold">Lihat</a>
            <form method="POST" action="/admin/berita/{{ $b->id }}/tampil">@csrf
              <button class="font-semibold text-green-800">{{ $b->terbit ? 'Sembunyikan' : 'Tampilkan' }}</button>
            </form>
            <form method="POST" action="/admin/berita/{{ $b->id }}/hapus" data-konfirmasi="Hapus berita ini?">@csrf
              <button class="text-red-600">Hapus</button>
            </form>
          </div>

          <details class="pt-2">
            <summary class="text-xs text-green-800 cursor-pointer list-none [&::-webkit-details-marker]:hidden">Ubah</summary>
            <form method="POST" action="/admin/berita/{{ $b->id }}/ubah" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-2 mt-2">
              @csrf
              <input name="judul" value="{{ $b->judul }}" class="md:col-span-2 border rounded-lg px-3 py-1.5 text-sm" required>
              <input name="ringkasan" value="{{ $b->ringkasan }}" class="md:col-span-2 border rounded-lg px-3 py-1.5 text-sm" required>
              <textarea name="isi" rows="4" class="md:col-span-2 border rounded-lg px-3 py-1.5 text-sm">{{ $b->isi }}</textarea>
              <input type="date" name="tanggal" value="{{ $b->tanggal->toDateString() }}" class="border rounded-lg px-3 py-1.5 text-sm" required>
              <input type="file" name="foto" accept="image/*" class="text-xs">
              <button class="md:col-span-2 bg-green-800 text-white rounded-lg py-1.5 text-sm font-semibold">Simpan perubahan</button>
            </form>
          </details>
        </div>
      </div>
    @empty
      <p class="text-gray-500">Belum ada berita. Tambahkan yang pertama.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $daftar->links() }}</div>
@endsection