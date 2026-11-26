<?php

namespace Tests\Unit;

use App\Models\Barang;
use App\Models\Kategori;
use PHPUnit\Framework\TestCase;

class BarangFoodConditionTest extends TestCase
{
    public function test_food_condition_values_are_shown_with_food_labels(): void
    {
        $kategori = new Kategori(['nama' => 'Makanan & Minuman']);

        $barangBaru = new Barang(['kondisi' => 'Baru']);
        $barangBaru->setRelation('kategori', $kategori);
        $barangLayak = new Barang(['kondisi' => 'Layak Pakai']);
        $barangLayak->setRelation('kategori', $kategori);

        $this->assertSame('Kemasan utuh', $barangBaru->labelKondisi());
        $this->assertSame('Layak konsumsi', $barangLayak->labelKondisi());
    }

    public function test_non_food_conditions_keep_their_existing_labels(): void
    {
        $barang = new Barang(['kondisi' => 'Perlu Perbaikan']);
        $barang->setRelation('kategori', new Kategori(['nama' => 'Elektronik']));

        $this->assertSame('Perlu Perbaikan', $barang->labelKondisi());
    }

    public function test_quantity_label_formats_whole_and_fractional_amounts(): void
    {
        $whole = new Barang(['jumlah' => '20.00', 'satuan' => 'paket']);
        $fractional = new Barang(['jumlah' => '1.50', 'satuan' => 'kg']);

        $this->assertSame('20 paket', $whole->jumlahLabel());
        $this->assertSame('1,5 kg', $fractional->jumlahLabel());
    }

    public function test_quantity_label_is_empty_for_legacy_items_without_quantity(): void
    {
        $barang = new Barang;

        $this->assertNull($barang->jumlahLabel());
    }
}
