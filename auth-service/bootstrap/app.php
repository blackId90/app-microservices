<?php

use App\Exceptions\Handler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // web: __DIR__.'/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(
            // at: '*',
            at: [
                //* Rentang IP internal Docker (mencakup 172.17.x.x s/d 172.31.x.x)
                '172.16.0.0/12',

                //* Opsional: Jika Anda menggunakan Docker Swarm atau kustom network tertentu
                '10.0.0.0/8',
                '192.168.0.0/16',
            ],
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB
        );
        $middleware->trustHosts(at: [
            '127.0.0.1',
            'localhost',
            'auth-service:8000',
            'auth-service',
        ]);

        // 🚀 Pastikan AssignRequestIdMiddleware selalu jalan di semua request (urutan main prepend dari bawah ke atas)
        $middleware->prepend(\App\Http\Middleware\SetLocaleMiddleware::class); // 2
        $middleware->prepend(\App\Http\Middleware\AssignRequestIdMiddleware::class); // 1

        $middleware->alias([
            // 'debug.profiling' => DebugProfilingMiddleware::class,
            // 'assign.request' => \App\Http\Middleware\AssignRequestIdMiddleware::class,
            // 'assign.locale' => \App\Http\Middleware\SetLocaleMiddleware::class,
            'jwt' => \App\Http\Middleware\JwtMiddleware::class,
            'api.key' => \App\Http\Middleware\ApiKeyMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Handler::handle($exceptions);
    })->create();
