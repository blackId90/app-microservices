<?php

namespace App\Providers;

use App\Auth\CachedUserProvider;
use App\Auth\JwtCachedGuard;
use App\Services\JwtRedisService;
use App\Services\UserCacheService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\JWT;

class AuthServiceProvider extends ServiceProvider {

    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void {
        //* Register custom UserProvider
        Auth::provider('cached_user', function ($app, array $config) {
            return new CachedUserProvider(userCache: $app->make(UserCacheService::class));
        });

        //* Register custom Guard
        Auth::extend('jwt_cached', function ($app, string $name, array $config) {
            $guard = new JwtCachedGuard(
                provider: Auth::createUserProvider($config['provider']),
                request: $app->make('request'),
                jwtRedis: $app->make(JwtRedisService::class),
                userCache: $app->make(UserCacheService::class),
                jwt: $app->make(JWT::class),
            );

            //* Inject request via service container agar bisa di-refresh
            $app->refresh('request', $guard, 'setRequest');

            return $guard;
        });
    }
}
