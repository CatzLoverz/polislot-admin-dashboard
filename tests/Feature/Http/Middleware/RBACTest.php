<?php

namespace Tests\Feature\Http\Middleware;

use App\Http\Middleware\RBAC;
use App\Models\User;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RBACTest extends TestCase
{
    #[Test]
    public function it_redirects_unauthenticated_web_users_to_login()
    {
        $request = Request::create('/admin/dashboard', 'GET');

        $middleware = new RBAC;

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
    }

    #[Test]
    public function it_returns_401_for_unauthenticated_api_users()
    {
        $request = Request::create('/api/test', 'GET');
        $request->headers->set('Accept', 'application/json');

        $middleware = new RBAC;

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        });

        $this->assertEquals(401, $response->getStatusCode());
    }

    #[Test]
    public function it_allows_authorized_role()
    {
        $user = User::factory()->make(['role' => 'admin']);
        $request = Request::create('/admin/dashboard', 'GET');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new RBAC;

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'admin');

        $this->assertEquals('OK', $response->getContent());
    }

    #[Test]
    public function it_blocks_unauthorized_role_for_api()
    {
        $user = User::factory()->make(['role' => 'user']);
        $request = Request::create('/api/admin', 'GET');
        $request->headers->set('Accept', 'application/json');
        $request->setUserResolver(function () use ($user) {
            return $user;
        });

        $middleware = new RBAC;

        $response = $middleware->handle($request, function ($req) {
            return response('OK');
        }, 'admin');

        $this->assertEquals(403, $response->getStatusCode());
    }
}
