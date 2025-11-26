<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Services\PointService;
use App\Services\TierService;
use App\Services\MissionService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register TierService dulu (karena PointService butuh TierService)
        $this->app->singleton(TierService::class, function ($app) {
            return new TierService();
        });

        // Register PointService dengan TierService sebagai dependency
        $this->app->singleton(PointService::class, function ($app) {
            return new PointService($app->make(TierService::class));
        });

        $this->app->singleton(MissionService::class, function ($app) {
        return new MissionService($app->make(PointService::class));
    });

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') {
            URL::forceScheme('https');
        }
    }
}
