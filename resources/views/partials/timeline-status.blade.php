@php
  $kelasStatus = [
    'selesai' => 'bg-green-700 text-white',
    'aktif' => 'bg-blue-700 text-white ring-4 ring-blue-100',
    'menunggu' => 'bg-gray-200 text-gray-600',
    'ditolak' => 'bg-red-600 text-white',
    'dibatalkan' => 'bg-gray-400 text-white',
  ];
  $ikonStatus = [
    'selesai' => 'cek',
    'ditolak' => 'peringatan',
    'dibatalkan' => 'tutup',
  ];
@endphp
<div class="mt-3 rounded-xl bg-gray-50 p-3" aria-label="Pelacakan status">
  <h3 class="text-xs font-bold text-gray-700 mb-3">Perkembangan status</h3>
  <ol class="space-y-2">
    @foreach ($statusTahapan as $tahap)
      <li class="flex gap-3">
        <div class="flex flex-col items-center">
          <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $kelasStatus[$tahap['status']] ?? $kelasStatus['menunggu'] }}" @if ($tahap['status'] === 'aktif') aria-current="step" @endif>
            @if (isset($ikonStatus[$tahap['status']]))
              <x-ikon :nama="$ikonStatus[$tahap['status']]" class="w-3 h-3" />
            @else
              {{ $loop->iteration }}
            @endif
          </span>
          @unless ($loop->last)
            <span class="mt-1 w-px flex-1 bg-gray-200" aria-hidden="true"></span>
          @endunless
        </div>
        <div class="pb-2">
          <p class="text-xs font-semibold text-gray-800">{{ $tahap['judul'] }}</p>
          @if (! empty($tahap['keterangan']))
            <p class="text-xs text-gray-500">{{ $tahap['keterangan'] }}</p>
          @endif
        </div>
      </li>
    @endforeach
  </ol>
</div>
