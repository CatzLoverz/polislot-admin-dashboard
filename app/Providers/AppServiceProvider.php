<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)
            ->by($request->user()?->id ?: $request->ip())
            ->response(function () use ($request) {

                Log::warning('[SERVICE RateLimiter] API rate limit exceeded', [
                    'ip'        => $request->ip(),
                    'user_id'   => $request->user()?->id,
                    'path'      => $request->path(),
                    'method'    => $request->method(),
                ]);

                return response()->json([
                    'message' => 'Too Many Requests'
                ], 429);
            });
        });
    }
}
