<div class="baris-barang border rounded-xl p-4 space-y-3 bg-gray-50">
  <div class="flex justify-between items-center">
    <div class="font-semibold text-sm">Barang <span class="no"></span></div>
    <button type="button" data-aksi="hapus" class="text-xs text-red-600">Hapus</button>
  </div>

  <input name="barang[{{ $i }}][judul]" value="{{ old("barang.$i.judul") }}" placeholder="Nama barang" class="w-full border rounded-lg px-3 py-2 text-sm bg-white" required>

  <div class="grid grid-cols-2 gap-3">
    <select name="barang[{{ $i }}][kategori_id]" class="border rounded-lg px-3 py-2 text-sm bg-white" required>
      <option value="">Kategori</option>
      @foreach ($kategori as $k)
        <option value="{{ $k->id }}" @selected(old("barang.$i.kategori_id") == $k->id)>{{ $k->nama }}</option>
      @endforeach
    </select>
    <select name="barang[{{ $i }}][kondisi]" class="border rounded-lg px-3 py-2 text-sm bg-white" required>
      @foreach (['Baru', 'Layak Pakai', 'Perlu Perbaikan'] as $kd)
        <option @selected(old("barang.$i.kondisi", 'Layak Pakai') == $kd)>{{ $kd }}</option>
      @endforeach
    </select>
  </div>

  <textarea name="barang[{{ $i }}][deskripsi]" rows="2" placeholder="Deskripsi (kondisi, ukuran, kekurangan jika ada)" class="w-full border rounded-lg px-3 py-2 text-sm bg-white" required>{{ old("barang.$i.deskripsi") }}</textarea>

  <div>
    <div class="text-xs font-semibold text-gray-700 mb-1">Foto barang <span class="font-normal text-gray-500">(wajib 3 foto, maks. 2 MB per foto)</span></div>
    <div class="grid grid-cols-3 gap-2">
      @foreach (['Tampak depan', 'Tampak samping / detail', 'Kondisi / kekurangan'] as $n => $petunjuk)
        <label class="slot-foto block border-2 border-dashed rounded-lg p-2 text-center bg-white">
          <img class="hidden w-full h-20 object-cover rounded mb-1" alt="">
          <span class="block text-[11px] text-gray-500">{{ $n + 1 }}. {{ $petunjuk }}</span>
          <input type="file" name="barang[{{ $i }}][foto][{{ $n }}]" accept="image/*" class="in-foto block w-full text-[11px] mt-1" required>
        </label>
      @endforeach
    </div>
  </div>

  <div class="slot-video">
    <div class="text-xs font-semibold text-gray-700 mb-1">Video barang <span class="font-normal text-gray-500">(wajib 1 video, maks. 60 detik dan 20 MB)</span></div>
    <input type="file" name="barang[{{ $i }}][video]" accept="video/*" class="in-video w-full text-sm" required>
    <p class="info-video text-xs text-green-700 mt-1"></p>
    <p class="text-[11px] text-gray-500">Tunjukkan barang dari beberapa sisi. Untuk elektronik, perlihatkan barang menyala dan berfungsi.</p>
  </div>
</div>