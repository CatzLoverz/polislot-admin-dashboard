<?php

namespace Tests\Unit\Service;

use App\Services\IpLocationService;
use Illuminate\Http\Request;
use Tests\TestCase;

class IpLocationServiceTest extends TestCase
{
    private IpLocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new IpLocationService;
    }

    public function test_it_extracts_public_ip_from_cf_connecting_ip(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('CF-Connecting-IP', '203.0.113.195');

        $ip = $this->service->getRealIp($request);

        $this->assertEquals('203.0.113.195', $ip);
    }

    public function test_it_extracts_public_ip_from_x_real_ip(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('X-Real-IP', '198.51.100.1');

        $ip = $this->service->getRealIp($request);

        $this->assertEquals('198.51.100.1', $ip);
    }

    public function test_it_extracts_first_public_ip_from_x_forwarded_for(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('X-Forwarded-For', '203.0.113.50, 172.18.0.1');

        $ip = $this->service->getRealIp($request);

        $this->assertEquals('203.0.113.50', $ip);
    }

    public function test_it_extracts_private_lan_ip_from_x_real_ip_when_in_local_or_docker_network(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('X-Real-IP', '192.168.1.15');

        $ip = $this->service->getRealIp($request);

        $this->assertEquals('192.168.1.15', $ip);
    }

    public function test_it_extracts_client_lan_ip_from_x_forwarded_for_when_behind_docker_proxy(): void
    {
        $request = Request::create('/', 'GET');
        $request->headers->set('X-Forwarded-For', '192.168.1.25, 172.18.0.2');

        $ip = $this->service->getRealIp($request);

        $this->assertEquals('192.168.1.25', $ip);
    }

    public function test_it_falls_back_to_request_ip_when_no_headers_present(): void
    {
        $request = Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);

        $ip = $this->service->getRealIp($request);

        $this->assertEquals('127.0.0.1', $ip);
    }

    public function test_is_public_ip_and_is_valid_ip(): void
    {
        // Public IP
        $this->assertTrue($this->service->isPublicIp('203.0.113.1'));
        $this->assertTrue($this->service->isValidIp('203.0.113.1'));

        // Private IP
        $this->assertFalse($this->service->isPublicIp('192.168.1.1'));
        $this->assertFalse($this->service->isPublicIp('10.0.0.1'));
        $this->assertFalse($this->service->isPublicIp('172.18.0.2'));
        $this->assertFalse($this->service->isPublicIp('127.0.0.1'));

        $this->assertTrue($this->service->isValidIp('192.168.1.1'));
        $this->assertTrue($this->service->isValidIp('10.0.0.1'));
        $this->assertTrue($this->service->isValidIp('172.18.0.2'));
        $this->assertTrue($this->service->isValidIp('127.0.0.1'));

        // Invalid IP
        $this->assertFalse($this->service->isPublicIp('invalid-ip'));
        $this->assertFalse($this->service->isValidIp('invalid-ip'));
    }
}
