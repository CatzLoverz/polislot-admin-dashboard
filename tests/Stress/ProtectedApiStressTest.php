<?php

declare(strict_types=1);

use App\Models\FeedbackCategory;
use App\Models\InfoBoard;
use App\Models\ParkArea;
use App\Models\User;
use App\Http\Middleware\ApiEncryption;
use Illuminate\Foundation\Testing\WithoutMiddleware;

uses(WithoutMiddleware::class);

/**
 * Stress test endpoint protected API (pakai auth + bypass encryption).
 * Tidak memerlukan header X-Session-Key karena ApiEncryption dimatikan di test.
 */
beforeEach(function () {
    $this->withoutMiddleware([ApiEncryption::class]);
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('info-board endpoint handles 50 stress iterations', function () {
    InfoBoard::create([
        'user_id' => $this->user->user_id,
        'info_title' => 'Stress Info',
        'info_content' => 'Stress test content',
    ]);

    $iterations = 50;
    $successCount = 0;
    $failCount = 0;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $response = $this->getJson('/api/info-board');
        $times[] = (microtime(true) - $start) * 1000;

        if ($response->status() === 200) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);
    $failRate = ($failCount / $iterations) * 100;

    dump(sprintf(
        'InfoBoard Auth Stress: %d req → %d/%d | avg=%.1fms p95=%.1fms | fail=%.1f%%',
        $iterations, $successCount, $failCount, $avg, $p95, $failRate
    ));

    expect($failRate)->toBeLessThan(5.0);
});

test('profile endpoint handles 50 stress iterations', function () {
    $iterations = 50;
    $successCount = 0;
    $failCount = 0;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $response = $this->getJson('/api/profile');
        $times[] = (microtime(true) - $start) * 1000;

        if ($response->status() === 200) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);
    $failRate = ($failCount / $iterations) * 100;

    dump(sprintf(
        'Profile Auth Stress: %d req → %d/%d | avg=%.1fms p95=%.1fms | fail=%.1f%%',
        $iterations, $successCount, $failCount, $avg, $p95, $failRate
    ));

    expect($failRate)->toBeLessThan(5.0);
});

test('map-visualization endpoint handles 50 stress iterations', function () {
    ParkArea::create(['park_area_name' => 'Stress Area', 'park_area_code' => 'SA', 'park_area_data' => []]);

    $iterations = 50;
    $successCount = 0;
    $failCount = 0;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $response = $this->getJson('/api/map-visualization');
        $times[] = (microtime(true) - $start) * 1000;

        if ($response->status() === 200) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);
    $failRate = ($failCount / $iterations) * 100;

    dump(sprintf(
        'MapVisualization Auth Stress: %d req → %d/%d | avg=%.1fms p95=%.1fms | fail=%.1f%%',
        $iterations, $successCount, $failCount, $avg, $p95, $failRate
    ));

    expect($failRate)->toBeLessThan(5.0);
});

test('feedback-categories endpoint handles 50 stress iterations', function () {
    FeedbackCategory::create(['fbk_category_name' => 'Stress Category']);

    $iterations = 50;
    $successCount = 0;
    $failCount = 0;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $response = $this->getJson('/api/feedback-categories');
        $times[] = (microtime(true) - $start) * 1000;

        if ($response->status() === 200) {
            $successCount++;
        } else {
            $failCount++;
        }
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);
    $failRate = ($failCount / $iterations) * 100;

    dump(sprintf(
        'FeedbackCategories Auth Stress: %d req → %d/%d | avg=%.1fms p95=%.1fms | fail=%.1f%%',
        $iterations, $successCount, $failCount, $avg, $p95, $failRate
    ));

    expect($failRate)->toBeLessThan(5.0);
});
