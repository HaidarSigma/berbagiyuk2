@extends('admin.layout')
@section('judul', 'Peringatan Keamanan')
@section('isi')
  @php
    $nama = [
      'lonjakan_gagal'  => 'Lonjakan percobaan gagal',
      'banyak_terkunci' => 'Banyak PIN terkunci',
      'satu_ip'         => 'Satu alamat IP mencurigakan',
      'backup_gagal' => 'Backup gagal',
      'aset_berubah' => 'Integritas aset berubah',
    ];
  @endphp

  <div class="flex justify-between items-center mb-4">
    <p class="text-sm text-gray-600">Peringatan dibuat otomatis saat kejadian gagal melampaui ambang. Detailnya ada di <a href="/admin/log" class="text-green-800 font-semibold">Log Aktivitas</a>.</p>
    <form method="POST" action="/admin/peringatan/baca">@csrf
      <button class="text-sm font-semibold text-green-800">✔ Tandai semua dibaca</button>
    </form>
  </div>

  <div class="space-y-3">
    @forelse ($daftar as $p)
      <div class="bg-white rounded-2xl shadow-sm p-4 border-l-4 {{ $p->dibaca_at ? 'border-gray-200 opacity-70' : 'border-red-500' }}">
        <div class="flex flex-wrap items-center gap-2">
          <div class="font-bold">{{ $nama[$p->jenis] ?? $p->jenis }}</div>
          @unless ($p->dibaca_at)<span class="text-[11px] bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Baru</span>@endunless
        </div>
        <p class="text-sm text-gray-700 mt-1">{{ $p->pesan }}</p>
        <div class="text-xs text-gray-500 mt-1">{{ $p->created_at->locale('id')->translatedFormat('d M Y, H:i') }}</div>
      </div>
    @empty
      <p class="text-gray-500">Belum ada peringatan. Itu kabar baik.</p>
    @endforelse
  </div>
  <div class="mt-4">{{ $daftar->links() }}</div>
@endsection