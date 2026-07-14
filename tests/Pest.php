<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to alter it depending on the test you need to run.
|
*/

uses(\Tests\TestCase::class, RefreshDatabase::class)->in('Feature', 'Unit', 'Performance', 'Stress');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function stressEndpoint(string $path): string
{
    $base = rtrim((string) env('STRESS_BASE_URL', 'http://localhost:8000'), '/');

    return $base.'/'.ltrim($path, '/');
}

/**
 * Hitung percentile dari array values untuk analisis latensi.
 *
 * @param  array<int, float>  $values  Array nilai latensi dalam milidetik
 * @param  int  $percentile  Percentile yang dihitung (0-100)
 * @return float Nilai pada percentile yang diminta
 */
function percentile(array $values, int $percentile): float
{
    $sorted = $values;
    sort($sorted);
    $index = ceil(($percentile / 100) * count($sorted)) - 1;

    return $sorted[max(0, $index)];
}
