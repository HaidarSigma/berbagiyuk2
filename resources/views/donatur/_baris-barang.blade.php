<div class="baris-barang border rounded-xl p-4 space-y-3 bg-gray-50">
  <div class="flex justify-between items-center">
    <div class="font-semibold text-sm">Barang <span class="no"></span></div>
    <button type="button" onclick="hapus(this)" class="text-xs text-red-600">Hapus</button>
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
    <label class="text-xs text-gray-600">Foto barang (maks. 2 MB)</label>
    <input type="file" name="barang[{{ $i }}][foto]" accept="image/*" class="w-full text-sm mt-1" required>
  </div>
</div>