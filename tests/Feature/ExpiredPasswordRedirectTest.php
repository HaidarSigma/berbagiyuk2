<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExpiredPasswordRedirectTest extends TestCase
{
    public function test_expired_petugas_is_redirected_to_password_update_after_pin(): void
    {
        $user = new User;
        $user->forceFill([
            'id' => 1,
            'role' => 'petugas',
            'password_changed_at' => now()->subMonths(3),
            'created_at' => now()->subYear(),
        ]);

        $response = $this->actingAs($user)
            ->withSession(['pin_ok' => true])
            ->get('/');

        $response->assertRedirect('/password/kedaluwarsa');
    }
}
