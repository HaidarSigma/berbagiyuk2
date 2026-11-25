<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Gambar
{
    // Membaca gambar, meluruskan orientasi, memperkecil, dan menyiapkannya tanpa metadata (EXIF/GPS)
    private static function bersih(string $isi, ?string $mime, int $sisiMaks, ?string $jalur = null)
    {
        $img = @imagecreatefromstring($isi);
        if ($img === false) {
            abort(422, 'Gambar tidak dapat diproses. Gunakan JPG, PNG, atau WEBP yang valid.');
        }

        // Orientasi tersimpan di EXIF: diterapkan dulu sebelum metadata dibuang, supaya foto tidak miring
        if ($mime === 'image/jpeg' && $jalur && function_exists('exif_read_data')) {
            $exif  = @exif_read_data($jalur);
            $sudut = [3 => 180, 6 => -90, 8 => 90][is_array($exif) ? ($exif['Orientation'] ?? 1) : 1] ?? 0;
            if ($sudut) {
                $putar = imagerotate($img, $sudut, 0);
                if ($putar) {
                    $img = $putar;
                }
            }
        }

        $w      = imagesx($img);
        $h      = imagesy($img);
        $skala  = min(1, $sisiMaks / max($w, $h));
        $lebar  = max(1, (int) round($w * $skala));
        $tinggi = max(1, (int) round($h * $skala));

        // Kanvas baru berlatar putih: hanya piksel yang disalin, metadata tidak ikut
        $kanvas = imagecreatetruecolor($lebar, $tinggi);
        imagefill($kanvas, 0, 0, imagecolorallocate($kanvas, 255, 255, 255));
        imagecopyresampled($kanvas, $img, 0, 0, 0, 0, $lebar, $tinggi, $w, $h);
        imagedestroy($img);

        return $kanvas;
    }

    // Untuk unggahan baru: disimpan sebagai JPEG baru dengan nama acak, tanpa metadata
    public static function simpan(UploadedFile $berkas, string $folder, string $disk = 'public', int $sisiMaks = 1600): string
    {
        $kanvas = self::bersih(file_get_contents($berkas->getRealPath()), $berkas->getMimeType(), $sisiMaks, $berkas->getRealPath());

        ob_start();
        imagejpeg($kanvas, null, 85);
        $jpeg = ob_get_clean();
        imagedestroy($kanvas);

        $path = trim($folder, '/').'/'.Str::random(40).'.jpg';
        Storage::disk($disk)->put($path, $jpeg);

        return $path;
    }

    // Untuk berkas lama: ditulis ulang di tempat dengan format yang sama (nama berkas tidak berubah)
    public static function bersihkanBerkas(string $jalur, int $sisiMaks = 2400): bool
    {
        $mime = mime_content_type($jalur);
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return false;
        }

        $kanvas = self::bersih(file_get_contents($jalur), $mime, $sisiMaks, $jalur);

        ob_start();
        if ($mime === 'image/png') {
            imagepng($kanvas, null, 6);
        } elseif ($mime === 'image/webp') {
            imagewebp($kanvas, null, 85);
        } else {
            imagejpeg($kanvas, null, 85);
        }
        $data = ob_get_clean();
        imagedestroy($kanvas);

        file_put_contents($jalur, $data);

        return true;
    }

    public static function punyaGps(string $jalur): bool
    {
        if (! function_exists('exif_read_data') || mime_content_type($jalur) !== 'image/jpeg') {
            return false;
        }

        $e = @exif_read_data($jalur, 'GPS', true);

        return is_array($e) && ! empty($e['GPS']);
    }
}