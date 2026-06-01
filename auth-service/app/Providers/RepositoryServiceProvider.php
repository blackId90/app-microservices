<?php

namespace App\Providers;

// use App\Services\Applications\AuthRoleService;
// use App\Services\Applications\RegisterService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {

    /**
     * Register services.
     */
    public function register(): void {
        //* Manual binding auth user repository
        // $this->app->bind('App\Repositories\Interfaces\AuthUserRepositoryInterface', 'App\Repositories\AuthUserRepository');
        $this->app->bind(
            \App\Repositories\Interfaces\AuthUserRepositoryInterface::class,
            \App\Repositories\AuthUserRepository::class
        );

        //* Manual binding auth role repository
        $this->app->bind(
            \App\Repositories\Interfaces\AuthRoleRepositoryInterface::class,
            \App\Repositories\AuthRoleRepository::class
        );

        //* Manual binding auth role permission repository
        $this->app->bind(
            \App\Repositories\Interfaces\AuthRolePermissionRepositoryInterface::class,
            \App\Repositories\AuthRolePermissionRepository::class
        );

        //* Manual binding auth permission repository
        $this->app->bind(
            \App\Repositories\Interfaces\AuthPermissionRepositoryInterface::class,
            \App\Repositories\AuthPermissionRepository::class
        );

        //* Manual binding login attempt repository
        $this->app->bind(
            \App\Repositories\Interfaces\LoginAttemptRepositoryInterface::class,
            \App\Repositories\LoginAttemptRepository::class
        );

        //* Manual binding application services
        /*
        $this->app->bind(
            \App\Services\Applications\RegisterService::class,
            \App\Services\Applications\RegisterService::class
        );

        //* Bind AuthRoleService as singleton
        $this->app->singleton(
            \App\Services\Applications\AuthRoleService::class,
            \App\Services\Applications\AuthRoleService::class
        );
        */
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        //
    }
}
