@extends('admin.layout')
@section('judul', 'Kelola Pengguna')
@section('isi')
  @php
    $warnaRole = ['admin' => 'bg-purple-100 text-purple-800', 'petugas' => 'bg-blue-100 text-blue-800', 'donatur' => 'bg-green-100 text-green-800', 'penerima' => 'bg-orange-100 text-orange-800'];
  @endphp

  @if ($errors->any())
    <div class="mb-4 bg-red-50 text-red-700 text-sm rounded-lg p-3">@foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
  @endif

  <details class="bg-white rounded-2xl shadow-sm mb-5">
    <summary class="cursor-pointer list-none flex w-full items-center justify-center px-5 py-3 font-semibold text-green-800">
      <span aria-hidden="true">+</span> Tambah akun petugas
    </summary>
    <form method="POST" action="/admin/pengguna/petugas" class="p-5 pt-2 grid md:grid-cols-2 gap-3">
      @csrf
      <input name="name" value="{{ old('name') }}" placeholder="Nama lengkap" class="border rounded-lg px-3 py-2 text-sm" required>
      <input type="email" name="email" value="{{ old('email') }}" placeholder="Email" class="border rounded-lg px-3 py-2 text-sm" required>
      <input name="no_hp" value="{{ old('no_hp') }}" placeholder="No. HP" class="border rounded-lg px-3 py-2 text-sm" required>
      <input type="password" name="password" placeholder="Password awal (min. 12, huruf besar-kecil, angka, simbol)" class="border rounded-lg px-3 py-2 text-sm" required>
      <button class="md:col-span-2 bg-green-800 text-white rounded-lg py-2 text-sm font-semibold hover:bg-green-900">Buat Akun Petugas</button>
    </form>
  </details>

  <form method="GET" class="bg-white rounded-2xl shadow-sm p-4 grid sm:grid-cols-4 gap-3 mb-5">
    <input name="q" value="{{ request('q') }}" placeholder="Cari nama atau email..." class="sm:col-span-2 border rounded-lg px-3 py-2 text-sm">
    <select name="role" class="border rounded-lg px-3 py-2 text-sm">
      <option value="">Semua peran</option>
      @foreach (['donatur', 'penerima', 'petugas', 'admin'] as $rl)
        <option value="{{ $rl }}" @selected(request('role') == $rl)>{{ ucfirst($rl) }}</option>
      @endforeach
    </select>
    <button class="bg-green-800 text-white rounded-lg px-4 py-2 text-sm font-semibold">Terapkan</button>
  </form>

  <div class="bg-white rounded-2xl shadow-sm overflow-x-auto">
    <table class="w-full text-sm">
      <thead class="text-left text-xs text-gray-500 border-b">
        <tr><th class="p-3">Pengguna</th><th class="p-3">Peran</th><th class="p-3">Status</th><th class="p-3">Aksi</th></tr>
      </thead>
      <tbody>
        @foreach ($pengguna as $p)
          @php $boleh = $p->role !== 'admin' && $p->id !== auth()->id(); @endphp
          <tr class="border-b last:border-0 align-top">
            <td class="p-3">
              <div class="font-semibold">{{ $p->name }}</div>
              <div class="text-xs text-gray-500">{{ $p->email }}</div>
            </td>
            <td class="p-3"><span class="text-xs px-2 py-0.5 rounded-full {{ $warnaRole[$p->role] ?? 'bg-gray-100' }}">{{ ucfirst($p->role) }}</span></td>
            <td class="p-3">
              @if ($p->aktif)
                <span class="text-xs px-2 py-0.5 rounded-full bg-green-50 text-green-800">Aktif</span>
              @else
                <span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">Nonaktif</span>
                <div class="text-xs text-red-600 mt-1">{{ $p->alasan_nonaktif }}</div>
              @endif
            </td>
            <td class="p-3">
              @if (! $boleh)
                <span class="text-xs text-gray-400">Diatur atasan</span>
              @elseif ($p->aktif)
                <form method="POST" action="/admin/pengguna/{{ $p->id }}/nonaktifkan" class="flex gap-2" onsubmit="return confirm('Nonaktifkan akun ini?')">
                  @csrf
                  <input name="alasan_nonaktif" required maxlength="200" placeholder="Alasan" class="border rounded-lg px-2 py-1 text-xs w-40">
                  <button class="bg-red-600 text-white rounded-lg px-3 py-1 text-xs font-semibold hover:bg-red-700">Nonaktifkan</button>
                </form>
              @else
                <form method="POST" action="/admin/pengguna/{{ $p->id }}/aktifkan">
                  @csrf
                  <button class="bg-green-700 text-white rounded-lg px-3 py-1 text-xs font-semibold hover:bg-green-800">Aktifkan</button>
                </form>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $pengguna->links() }}</div>
@endsection