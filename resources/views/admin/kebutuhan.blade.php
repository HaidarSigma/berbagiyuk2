@extends('admin.layout')
@section('judul', 'Kebutuhan Mendesak')
@section('isi')
  <style>
    .no-marker summary,
    .no-marker summary::-webkit-details-marker {
      list-style: none;
      display: list-item;
      list-style-type: none;
      -webkit-appearance: none;
    }
    .no-marker summary::marker {
      display: none;
      content: "";
    }
  </style>

  @if ($errors->any())
    <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">@foreach (array_unique($errors->all()) as $e)<p>{{ $e }}</p>@endforeach</div>
  @endif

  <details class="no-marker bg-white rounded-2xl shadow-sm mb-5 border border-gray-200 overflow-hidden">
    <summary class="cursor-pointer list-none flex w-full items-center justify-center px-5 py-3 font-semibold text-green-800 bg-green-50 hover:bg-green-100 transition">
      Tambah kebutuhan
    </summary>
    <form method="POST" action="/admin/kebutuhan" enctype="multipart/form-data" class="p-5 pt-2 grid md:grid-cols-2 gap-3 bg-white">
      @csrf
      <input name="judul" value="{{ old('judul') }}" placeholder="Judul (contoh: Selimut untuk korban banjir)" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200" required>
      <input name="lokasi" value="{{ old('lokasi') }}" placeholder="Contoh: {{ config('wilayah.contoh') }}" class="border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200" required>
      <input name="keterangan" value="{{ old('keterangan') }}" placeholder="Keterangan singkat" class="md:col-span-2 border border-gray-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
      <div class="md:col-span-2">
        <label class="text-xs text-gray-500">Foto (opsional, maks. 2 MB)</label>
        <input type="file" name="foto" accept="image/*" class="w-full text-sm mt-1 border border-gray-200 rounded-xl px-3 py-2 bg-white">
      </div>
      <button class="md:col-span-2 bg-green-800 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-green-900 transition">Tambahkan</button>
    </form>
  </details>

  <div class="space-y-3">
    @forelse ($daftar as $k)
      <div class="bg-white rounded-2xl shadow-sm p-4 flex gap-4 border border-gray-200">
        <div class="w-20 h-20 shrink-0 rounded-xl bg-green-100 overflow-hidden flex items-center justify-center text-3xl">
          @if ($k->foto)<img src="{{ asset('storage/'.$k->foto) }}" class="w-full h-full object-cover" alt="">@else 📦 @endif
        </div>

        <div class="flex-1 min-w-0 space-y-2">
          <div class="flex flex-wrap items-center gap-2">
            <div class="font-bold text-gray-900">{{ $k->judul }}</div>
            <span class="text-[11px] px-2 py-0.5 rounded-full {{ $k->status === 'mendukung' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-800' }}">{{ $k->status === 'mendukung' ? 'Mendesak' : 'Terpenuhi' }}</span>
          </div>
          <p class="text-sm text-gray-600">{{ $k->keterangan }}</p>
          <div class="text-xs text-gray-500">📍 {{ $k->lokasi }} · {{ $k->created_at->locale('id')->diffForHumans() }}</div>

          <div class="flex flex-wrap gap-4 pt-1 text-sm">
            <form method="POST" action="/admin/kebutuhan/{{ $k->id }}/status">@csrf
              <button class="font-semibold text-green-800 hover:text-green-900">{{ $k->status === 'mendukung' ? 'Tandai terpenuhi' : 'Buka kembali' }}</button>
            </form>
            <form method="POST" action="/admin/kebutuhan/{{ $k->id }}/hapus" onsubmit="return confirm('Hapus kebutuhan ini?')">@csrf
              <button class="text-red-600 hover:text-red-700">Hapus</button>
            </form>
          </div>

          <details class="no-marker pt-1">
            <summary class="text-xs text-green-800 cursor-pointer list-none hover:text-green-900">Ubah</summary>
            <form method="POST" action="/admin/kebutuhan/{{ $k->id }}/ubah" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-2 mt-2">
              @csrf
              <input name="judul" value="{{ $k->judul }}" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200" required>
              <input name="lokasi" value="{{ $k->lokasi }}" class="border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200" required>
              <input name="keterangan" value="{{ $k->keterangan }}" class="md:col-span-2 border border-gray-200 rounded-lg px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-200">
              <input type="file" name="foto" accept="image/*" class="md:col-span-2 text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white">
              <button class="md:col-span-2 bg-green-800 text-white rounded-lg py-1.5 text-sm font-semibold hover:bg-green-900 transition">Simpan perubahan</button>
            </form>
          </details>
        </div>
      </div>
    @empty
      <p class="text-gray-500">Belum ada kebutuhan. Tambahkan yang pertama.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $daftar->links() }}</div>
@endsection