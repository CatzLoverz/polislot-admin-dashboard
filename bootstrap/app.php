<?php

use App\Http\Middleware\ApiEncryption;
use App\Http\Middleware\RBAC;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\TrustProxies;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(TrustProxies::class);
        $middleware->alias([
            # Default Laravel Middleware
            'auth' => Authenticate::class,
            'guest' => RedirectIfAuthenticated::class,
            # Custom Middleware
            'role' => RBAC::class,
            # API Encyption
            'encryptApi' => ApiEncryption::class,
        ]);
        
        // PRIORITAS MIDDLEWARE
        // Pastikan ApiEncryption jalan DULUAN sebelum Auth
        $middleware->priority([
            ApiEncryption::class,
            Authenticate::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login.form'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
