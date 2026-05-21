<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Providers\AuthServiceProvider;
use App\Http\Middleware\EnsureKycAndFace;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

    ->withProviders([
        AuthServiceProvider::class,
    ])

    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | Global Web Middleware
        |--------------------------------------------------------------------------
        */
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Global API Middleware
        |--------------------------------------------------------------------------
        */
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Route Middleware Aliases (Laravel 11/12 way)
        |--------------------------------------------------------------------------
        */
        $middleware->alias([
            'kyc.face' => EnsureKycAndFace::class,
        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })

    ->create();
