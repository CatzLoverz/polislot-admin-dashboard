<?php

declare(strict_types=1);

use App\Models\User;
use App\Http\Middleware\ApiEncryption;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Hash;

uses(WithoutMiddleware::class);

/**
 * Stress test endpoint auth publik (login).
 * Bypass encryptApi middleware karena simulasi internal Laravel.
 */
beforeEach(function () {
    $this->withoutMiddleware([ApiEncryption::class]);
});

test('login endpoint handles 50 stress iterations without crashing', function () {
    // Buat user valid
    $user = User::factory()->create([
        'email' => 'stress-login@example.com',
        'password' => Hash::make('Password123!'),
    ]);

    $iterations = 50;
    $successCount = 0;
    $failCount = 0;
    $times = [];
    $statusCodes = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $response = $this->postJson('/api/login-attempt', [
            'email' => 'stress-login@example.com',
            'password' => 'Password123!',
        ]);
        $elapsed = (microtime(true) - $start) * 1000;
        $times[] = $elapsed;

        $code = $response->status();
        $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;

        if ($code === 200) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf(
        'Login Stress: %d req → %d success / %d fail | avg=%.1fms p95=%.1fms | codes=%s',
        $iterations, $successCount, $failCount, $avg, $p95, json_encode($statusCodes)
    ));

    $failRate = ($failCount / $iterations) * 100;
    expect($failRate)->toBeLessThan(10.0, sprintf(
        'Login fail rate %.1f%% (%d/%d). Codes: %s',
        $failRate, $failCount, $iterations, json_encode($statusCodes)
    ));
});

test('login endpoint handles 50 invalid credentials stress test', function () {
    User::factory()->create(['email' => 'valid@example.com', 'password' => Hash::make('Password123!')]);

    $iterations = 50;
    $times = [];
    $statusCodes = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $response = $this->postJson('/api/login-attempt', [
            'email' => 'valid@example.com',
            'password' => 'wrong-password-'.$i,
        ]);
        $times[] = (microtime(true) - $start) * 1000;

        $code = $response->status();
        $statusCodes[$code] = ($statusCodes[$code] ?? 0) + 1;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf(
        'Login Invalid Stress: %d req | avg=%.1fms p95=%.1fms | codes=%s',
        $iterations, $avg, $p95, json_encode($statusCodes)
    ));
    // Harus tetap 401/422, bukan 500 (tidak crash)
    $crashCount = $statusCodes[500] ?? 0;
    expect($crashCount)->toBe(0, 'Login endpoint crash (500) saat handle invalid credentials');
});
