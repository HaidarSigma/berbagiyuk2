<?php

namespace Tests\Unit;

use App\Http\Middleware\SecurityHeaders;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersTest extends TestCase
{
    public function test_camera_is_allowed_for_the_same_origin_only(): void
    {
        $response = (new SecurityHeaders)->handle(
            Request::create('/'),
            fn () => new Response
        );

        $this->assertSame(
            'camera=(self), microphone=(), geolocation=()',
            $response->headers->get('Permissions-Policy')
        );
    }
}
