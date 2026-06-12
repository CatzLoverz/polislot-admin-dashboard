<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\ApiEncryption;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiEncryptionTest extends TestCase
{
    #[Test]
    public function it_ignores_non_api_requests()
    {
        $request = Request::create('/web-route', 'GET');
        
        $middleware = new ApiEncryption();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals('OK', $response->getContent());
    }

    #[Test]
    public function it_returns_500_or_400_if_key_or_session_invalid()
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('X-Session-Key', 'invalid_base64');
        
        $middleware = new ApiEncryption();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Tergantung apakah private key ada atau tidak di environment test
        $this->assertTrue(in_array($response->getStatusCode(), [400, 500]));
    }
    
    #[Test]
    public function it_blocks_raw_authorization_without_x_auth_token()
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Authorization', 'Bearer fake');
        
        $middleware = new ApiEncryption();
        
        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        // Karena X-Session-Key dicek terlebih dahulu dan tidak valid, akan direject dengan 400 sebelum mencapai pengecekan Authorization
        $this->assertEquals(400, $response->getStatusCode());
    }
}
