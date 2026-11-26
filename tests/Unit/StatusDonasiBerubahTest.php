<?php

namespace Tests\Unit;

use App\Notifications\StatusDonasiBerubah;
use PHPUnit\Framework\TestCase;

class StatusDonasiBerubahTest extends TestCase
{
    public function test_notification_uses_database_channel_and_keeps_its_display_data(): void
    {
        $notification = new StatusDonasiBerubah(
            'Donasi disetujui',
            'Barang disetujui dan menunggu dijemput.',
            '/barang-saya#barang-12'
        );

        $this->assertSame(['database'], $notification->via(new \stdClass));
        $this->assertSame([
            'judul' => 'Donasi disetujui',
            'pesan' => 'Barang disetujui dan menunggu dijemput.',
            'url' => '/barang-saya#barang-12',
        ], $notification->toDatabase(new \stdClass));
    }
}
