<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // ✅ Route middleware aliases (use these names in routes)
        $middleware->alias([
            'auth'        => \App\Http\Middleware\Authenticate::class,
            'authCliente' => \App\Http\Middleware\AuthenticateClient::class,
        ]);

        // (Optional) add to groups if you want them auto-applied:
        // $middleware->group('api', [
        //     \App\Http\Middleware\AuthenticateClient::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
