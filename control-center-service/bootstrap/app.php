<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 🚀 Pastikan AssignRequestIdMiddleware selalu jalan di semua request (urutan main prepend dari bawah ke atas)
        $middleware->prepend(\App\Http\Middleware\SetLocaleMiddleware::class); // 2
        $middleware->prepend(\App\Http\Middleware\AssignRequestIdMiddleware::class); // 1

        $middleware->alias([
            // 'debug.profiling' => DebugProfilingMiddleware::class,
            // 'auth' => \App\Http\Middleware\Authenticate::class, // Middleware with custom guard 'auth:api'
            'jwt' => \App\Http\Middleware\JwtMiddleware::class, // Middleware with JwtMiddleware 'jwt'
            'has.access' => \App\Http\Middleware\HasAccessMiddleware::class,
            'api.key' => \App\Http\Middleware\ApiKeyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        \App\Exceptions\Handler::handle($exceptions);
    })->create();
