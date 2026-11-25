<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class EmailVerificationResendTest extends TestCase
{
    public function test_email_transport_failure_keeps_user_on_verification_page(): void
    {
        $user = new class extends User {
            public function sendEmailVerificationNotification(): void
            {
                throw new TransportException('SMTP unavailable');
            }
        };
        $user->forceFill([
            'id' => 1,
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
        ]);

        Log::shouldReceive('warning')
            ->once()
            ->with('Gagal mengirim email verifikasi.', [
                'user_id' => 1,
                'exception' => 'SMTP unavailable',
            ]);

        $response = $this->actingAs($user)
            ->from('/email/verifikasi')
            ->post('/email/kirim-ulang');

        $response->assertRedirect('/email/verifikasi');
        $response->assertSessionHas('email_error');
    }
}
