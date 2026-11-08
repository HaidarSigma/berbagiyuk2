<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UserPasswordExpiryTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_password_is_not_expired_before_three_months(): void
    {
        Carbon::setTestNow('2026-11-08 12:00:00');
        $user = (new User)->forceFill(['password_changed_at' => now()->subMonths(3)->addSecond()]);

        $this->assertFalse($user->passwordKedaluwarsa());
    }

    public function test_password_is_expired_at_three_months(): void
    {
        Carbon::setTestNow('2026-11-08 12:00:00');
        $user = (new User)->forceFill(['password_changed_at' => now()->subMonths(3)]);

        $this->assertTrue($user->passwordKedaluwarsa());
    }

    public function test_password_expiry_uses_account_creation_date_when_change_date_is_missing(): void
    {
        Carbon::setTestNow('2026-11-08 12:00:00');
        $user = (new User)->forceFill(['created_at' => now()->subMonths(3)]);

        $this->assertTrue($user->passwordKedaluwarsa());
    }
}
