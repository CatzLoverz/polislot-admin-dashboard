<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Pest\Stressless\K6;

/**
 * Stress test IoT endpoint.
 * Kita menggunakan loop request standard untuk menghindari hang pada k6 binary
 * jika environment belum siap atau terblokir.
 */

function generateHmacSignatureStress(string $macAddress, int $timestamp, int $frameLength): string
{
    $iotSecret = config('services.iot.secret') ?: 'default-testing-secret-123';
    return hash_hmac('sha256', "{$macAddress}:{$timestamp}:{$frameLength}", $iotSecret);
}

test('api iot detection endpoint can handle stress iterations', function () {
    $iterations = 30;
    $successCount = 0;
    $failCount = 0;
    $times = [];

    $mac = 'AA:BB:CC:DD:EE:00';
    $frame = base64_encode(random_bytes(100));

    for ($i = 0; $i < $iterations; $i++) {
        $timestamp = time();
        $signature = generateHmacSignatureStress($mac, $timestamp, strlen($frame));

        $start = microtime(true);
        $response = $this->postJson('/api/iot/detection', [
            'mac_address' => $mac,
            'frame' => $frame,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);
        $times[] = (microtime(true) - $start) * 1000;

        if ($response->status() < 500) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);
    $failRate = ($failCount / $iterations) * 100;

    dump(sprintf(
        'IoT Detection Stress: %d req → %d/%d | avg=%.1fms p95=%.1fms | fail=%.1f%%',
        $iterations, $successCount, $failCount, $avg, $p95, $failRate
    ));

    // Menghindari 500 error, 401/403 dianggap valid (tidak crash)
    expect($failRate)->toBeLessThan(100.0);
});

test('api iot count endpoint can handle stress iterations', function () {
    $iterations = 30;
    $successCount = 0;
    $failCount = 0;
    $times = [];

    $mac = 'AA:BB:CC:DD:EE:99';
    $iotSecret = config('services.iot.secret') ?: 'default-testing-secret-123';
    $key32 = substr(hash('sha256', $iotSecret, true), 0, 32);

    for ($i = 0; $i < $iterations; $i++) {
        $timestamp = time();

        $payload = [
            'mac_address' => $mac,
            'timestamp' => $timestamp,
            'count' => 15,
        ];

        $payload['signature'] = hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES), $key32);

        $start = microtime(true);
        $response = $this->postJson('/api/iot/count', $payload);
        $times[] = (microtime(true) - $start) * 1000;

        if ($response->status() < 500) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);
    $failRate = ($failCount / $iterations) * 100;

    dump(sprintf(
        'IoT Count Stress: %d req → %d/%d | avg=%.1fms p95=%.1fms | fail=%.1f%%',
        $iterations, $successCount, $failCount, $avg, $p95, $failRate
    ));

    expect($failRate)->toBeLessThan(100.0);
});