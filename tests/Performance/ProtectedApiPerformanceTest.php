<?php

declare(strict_types=1);

use App\Models\FeedbackCategory;
use App\Models\InfoBoard;
use App\Models\ParkArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\ApiEncryption;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Performance');

/**
 * Performance test endpoint protected API (dengan auth, tanpa encryption).
 * Menggunakan Laravel test client (bukan k6), jadi bypass middleware manual.
 * Mengukur rata-rata latensi per-endpoint.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
    // Explicit bypass ApiEncryption middleware
    $this->withoutMiddleware([ApiEncryption::class]);
});

test('profile endpoint responds within acceptable time', function () {
    $iterations = 20;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/profile')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('Profile: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(300, "Profile P95 {$p95}ms melebihi 300ms threshold");
});

test('info-board endpoint responds within acceptable time', function () {
    InfoBoard::create([
        'user_id' => $this->user->user_id,
        'info_title' => 'Perf Test',
        'info_content' => 'Content for performance test',
    ]);

    $iterations = 20;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/info-board')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('InfoBoard: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(300, "InfoBoard P95 {$p95}ms melebihi 300ms threshold");
});

test('map-visualization index endpoint responds within acceptable time', function () {
    ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);

    $iterations = 10;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/map-visualization')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('MapVisualization Index: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(500, "MapVisualization P95 {$p95}ms melebihi 500ms threshold");
});

test('feedback-categories endpoint responds within acceptable time', function () {
    FeedbackCategory::create(['fbk_category_name' => 'General']);

    $iterations = 20;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/feedback-categories')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('FeedbackCategories: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(300, "FeedbackCategories P95 {$p95}ms melebihi 300ms threshold");
});

test('history endpoint responds within acceptable time', function () {
    $iterations = 10;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/history')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('History: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(300, "History P95 {$p95}ms melebihi 300ms threshold");
});

test('user-faq endpoint responds within acceptable time', function () {
    $iterations = 10;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/user-faq')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('UserFaq: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(300, "UserFaq P95 {$p95}ms melebihi 300ms threshold");
});

test('missions endpoint responds within acceptable time', function () {
    $iterations = 10;
    $times = [];

    for ($i = 0; $i < $iterations; $i++) {
        $start = microtime(true);
        $this->getJson('/api/missions')->assertStatus(200);
        $times[] = (microtime(true) - $start) * 1000;
    }

    $avg = array_sum($times) / count($times);
    $p95 = percentile($times, 95);

    dump(sprintf('Missions: avg=%.1fms, p95=%.1fms (%d iterations)', $avg, $p95, $iterations));

    expect($p95)->toBeLessThan(300, "Missions P95 {$p95}ms melebihi 300ms threshold");
});
