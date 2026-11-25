<?php

namespace App\Support;

class Totp
{
    public const ALFABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    public static function buatRahasia(int $byte = 20): string
    {
        return self::base32(random_bytes($byte));
    }

    public static function base32(string $biner): string
    {
        $bit = '';
        foreach (str_split($biner) as $c) {
            $bit .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bit, 5) as $k) {
            $out .= self::ALFABET[bindec(str_pad($k, 5, '0'))];
        }

        return $out;
    }

    private static function urai(string $teks): string
    {
        $teks = strtoupper(preg_replace('/[^A-Za-z2-7]/', '', $teks));

        $bit = '';
        foreach (str_split($teks) as $c) {
            $bit .= str_pad(decbin(strpos(self::ALFABET, $c)), 5, '0', STR_PAD_LEFT);
        }

        $out = '';
        foreach (str_split($bit, 8) as $b) {
            if (strlen($b) === 8) {
                $out .= chr(bindec($b));
            }
        }

        return $out;
    }

    public static function kode(string $rahasia, int $langkah): string
    {
        $h = hash_hmac('sha1', pack('N*', 0, $langkah), self::urai($rahasia), true);
        $o = ord($h[19]) & 0x0F;
        $n = ((ord($h[$o]) & 0x7F) << 24) | (ord($h[$o + 1]) << 16) | (ord($h[$o + 2]) << 8) | ord($h[$o + 3]);

        return str_pad((string) ($n % 1000000), 6, '0', STR_PAD_LEFT);
    }

    // Mengembalikan langkah waktu yang cocok (toleransi ±30 detik), atau null
    public static function periksa(string $rahasia, string $kode, int $toleransi = 1): ?int
    {
        $kode = preg_replace('/\D/', '', $kode);
        if (strlen($kode) !== 6) {
            return null;
        }

        $sekarang = intdiv(time(), 30);
        for ($i = -$toleransi; $i <= $toleransi; $i++) {
            if (hash_equals(self::kode($rahasia, $sekarang + $i), $kode)) {
                return $sekarang + $i;
            }
        }

        return null;
    }

    public static function uri(string $rahasia, string $email, string $penerbit = 'BerbagiYuk'): string
    {
        return 'otpauth://totp/'.rawurlencode($penerbit.':'.$email)
            .'?secret='.$rahasia.'&issuer='.rawurlencode($penerbit).'&algorithm=SHA1&digits=6&period=30';
    }
}