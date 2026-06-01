<?php

namespace App\Providers;

use App\Auth\CacheUserProvider;
use App\Auth\JwtGuard;
use App\Services\JwtRedisService;
use App\Services\JwtTokenService;
use App\Services\UserCacheService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider {
    /**
     * Register services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        //* Register custom user provider
        Auth::provider('cache', function (Application $app, array $config) {
            return new CacheUserProvider(
                $app->make(UserCacheService::class)
            );
        });

        //* Register custom guard
        Auth::extend('jwt', function (Application $app, string $name, array $config) {
            $provider = Auth::createUserProvider($config['provider']);

            return new JwtGuard(
                $provider,
                $app->make('request'),
                $app->make(JwtTokenService::class),
                $app->make(JwtRedisService::class)
            );
        });
    }
}
