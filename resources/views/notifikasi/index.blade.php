@extends('layouts.situs')
@section('judul', 'Notifikasi - BerbagiYuk')
@section('isi')
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h1 class="text-2xl font-extrabold">Notifikasi</h1>
      <p class="mt-1 text-sm text-gray-500">Pembaruan status barang dan permintaanmu.</p>
    </div>
    @if (auth()->user()->unreadNotifications()->exists())
      <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
        @csrf
        <button class="rounded-lg border bg-white px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Tandai semua dibaca</button>
      </form>
    @endif
  </div>

  <div class="mt-5 space-y-3">
    @forelse ($notifikasi as $item)
      <form method="POST" action="{{ route('notifikasi.buka', $item->id) }}">
        @csrf
        <button type="submit" class="flex w-full items-start gap-3 rounded-xl border {{ $item->read_at ? 'border-gray-100 bg-white' : 'border-blue-200 bg-blue-50' }} p-4 text-left">
          <x-ikon :nama="$item->read_at ? 'dokumen' : 'bel'" class="mt-0.5 w-5 h-5 shrink-0 {{ $item->read_at ? 'text-gray-400' : 'text-blue-700' }}" />
          <span class="min-w-0 flex-1">
            <span class="flex flex-wrap items-center gap-2">
              <span role="heading" aria-level="2" class="font-bold text-gray-900">{{ $item->data['judul'] ?? 'Pembaruan status' }}</span>
              @unless ($item->read_at)
                <span class="rounded-full bg-blue-700 px-2 py-0.5 text-[10px] font-semibold text-white">Baru</span>
              @endunless
            </span>
            <span class="mt-1 block text-sm text-gray-600">{{ $item->data['pesan'] ?? '' }}</span>
            <span class="mt-2 flex flex-wrap items-center justify-between gap-2">
              <time class="text-xs text-gray-400">{{ $item->created_at->locale('id')->diffForHumans() }}</time>
            </span>
          </span>
        </button>
      </form>
    @empty
      <div class="rounded-xl bg-white p-6 text-center text-sm text-gray-500 shadow-sm">Belum ada notifikasi.</div>
    @endforelse
  </div>

  <div class="mt-4">{{ $notifikasi->links() }}</div>
@endsection
