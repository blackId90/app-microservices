<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider {

    /**
     * Register services.
     */
    public function register(): void {
        //* Manual binding user repository
        // $this->app->bind('App\Repositories\Interfaces\UserRepositoryInterface', 'App\Repositories\UserRepository');
        $this->app->bind(
            \App\Repositories\Interfaces\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );

        //* Manual binding company repository
        $this->app->bind(
            \App\Repositories\Interfaces\CompanyRepositoryInterface::class,
            \App\Repositories\CompanyRepository::class
        );

        //* Manual binding currency repository
        $this->app->bind(
            \App\Repositories\Interfaces\CurrencyRepositoryInterface::class,
            \App\Repositories\CurrencyRepository::class
        );

        //* Manual binding continents repository
        $this->app->bind(
            \App\Repositories\Interfaces\ContinentRepositoryInterface::class,
            \App\Repositories\ContinentRepository::class
        );

        //* Manual binding countries repository
        $this->app->bind(
            \App\Repositories\Interfaces\CountryRepositoryInterface::class,
            \App\Repositories\CountryRepository::class
        );

        //* Manual binding languages repository
        $this->app->bind(
            \App\Repositories\Interfaces\LanguageRepositoryInterface::class,
            \App\Repositories\LanguageRepository::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void {
        //
    }
}
