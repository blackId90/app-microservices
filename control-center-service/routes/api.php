<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1/control-center')
    ->group(function () {
        //* Public routes for development
        Route::prefix('dev')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\HealthController::class, 'index'])
                    ->name('read.check_api');
                Route::get('/health', [\App\Http\Controllers\Api\HealthController::class, 'health'])
                    ->name('read.check_health');
            });

        //* Public internal routes with prefix internal (Communication from Services)
        Route::prefix('internal/public')
            ->middleware('api.key')
            ->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\HealthController::class, 'index'])
                    ->name('read.internal_check_api');
                Route::get('/health', [\App\Http\Controllers\Api\HealthController::class, 'health'])
                    ->name('read.internal_check_health');

                //* Register
                Route::post('/register', [\App\Http\Controllers\Api\InternalRegisterController::class, 'register'])
                    ->name('create.internal_register');
                Route::get('/email/verify/{company_id}/{key_email}', [\App\Http\Controllers\Api\InternalRegisterController::class, 'verify'])
                    ->name('update.verification.verify');
                Route::delete('/register/{company_id}/{user_auth_user_id}', [\App\Http\Controllers\Api\InternalRegisterController::class, 'destroyRegister'])
                    ->name('delete.internal_register');
            });

        //* Protected routes with JWT Redis middleware
        // Route::middleware(['auth:api', 'has.access'])
        Route::middleware(['jwt', 'has.access'])
            ->group(function () {
                // dd('routes', request()->bearerToken());

                //* Internal routes with prefix internal (Communication from Services)
                Route::prefix('internal')
                    ->middleware('api.key')
                    // ->name('users_')
                    ->group(function () {
                        // Route::get('/auth-user', [\App\Http\Controllers\Api\AuthController::class, 'getAuthProfile'])->name('read_control_center_profile:2');
                        Route::get('/auth-user', [\App\Http\Controllers\Api\AuthController::class, 'getAuthProfile'])
                            ->name('read.signin_profil_user');
                        Route::delete('/auth-user', [\App\Http\Controllers\Api\AuthController::class, 'destroyAuthProfile'])
                            ->name('delete.signin_profil_user:auto:4');
                    });

                //* Admin routes for token management
                Route::get('/management-tokens', [\App\Http\Controllers\Api\TokenManagementController::class, 'index'])
                    ->name('browse.token_managements');
                Route::post('/management-tokens/{authUserId}', [\App\Http\Controllers\Api\TokenManagementController::class, 'store'])
                    ->name('create.token_managements');
                Route::delete('/management-tokens/{authUserId}', [\App\Http\Controllers\Api\TokenManagementController::class, 'destroy'])
                    ->name('delete.token_managements');

                //* Admin routes for permissions management
                Route::prefix('auth_permissions')->group(function () {
                    //* Options
                    Route::get('/options/permissions', [\App\Http\Controllers\Api\PermissionController::class, 'optionPermissions'])
                        ->name('options.permissions.auth_permissions');

                    Route::get('/', [\App\Http\Controllers\Api\PermissionController::class, 'index'])
                        ->name('browse.auth_permissions');
                    Route::post('/', [\App\Http\Controllers\Api\PermissionController::class, 'store'])
                        ->name('create.auth_permissions');
                    Route::get('/{authPermissionId}', [\App\Http\Controllers\Api\PermissionController::class, 'show'])
                        ->name('read.auth_permissions');
                    Route::put('/{authPermissionId}', [\App\Http\Controllers\Api\PermissionController::class, 'update'])
                        ->name('update.auth_permissions');
                    Route::delete('/{authPermissionId}', [\App\Http\Controllers\Api\PermissionController::class, 'destroy'])
                        ->name('delete.auth_permissions');
                });

                //* Admin routes for roles management
                Route::prefix('auth_roles')->group(function () {
                    //* Options
                    Route::get('/options/permissions', [\App\Http\Controllers\Api\RoleController::class, 'optionPermissions'])
                        ->name('options.permissions.auth_roles');

                    Route::get('/', [\App\Http\Controllers\Api\RoleController::class, 'index'])
                        ->name('browse.auth_roles');
                    Route::post('/', [\App\Http\Controllers\Api\RoleController::class, 'store'])
                        ->name('create.auth_roles');
                    Route::get('/{authRoleId}', [\App\Http\Controllers\Api\RoleController::class, 'show'])
                        ->name('read.auth_roles');
                    Route::put('/{authRoleId}', [\App\Http\Controllers\Api\RoleController::class, 'update'])
                        ->name('update.auth_roles');
                    Route::delete('/{authRoleId}', [\App\Http\Controllers\Api\RoleController::class, 'destroy'])
                        ->name('delete.auth_roles');
                });

                //* Admin routes for users management
                Route::prefix('users')->group(function () {
                    //* Options
                    Route::get('/options/roles', [\App\Http\Controllers\Api\UserController::class, 'optionRoles'])
                        ->name('options.roles.users');

                    Route::get('/', [\App\Http\Controllers\Api\UserController::class, 'index'])
                        ->name('browse.users');
                    Route::post('/', [\App\Http\Controllers\Api\UserController::class, 'store'])
                        ->name('create.users');
                    Route::get('/{userId}', [\App\Http\Controllers\Api\UserController::class, 'show'])
                        ->name('read.users');
                    Route::put('/{userId}', [\App\Http\Controllers\Api\UserController::class, 'update'])
                        ->name('update.users');
                    Route::delete('/{userId}', [\App\Http\Controllers\Api\UserController::class, 'destroy'])
                        ->name('delete.users');
                });

                //* Admin routes for currencies management
                Route::prefix('currencies')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\CurrencyController::class, 'index'])
                        ->name('browse.currencies');
                    Route::post('/', [\App\Http\Controllers\Api\CurrencyController::class, 'store'])
                        ->name('create.currencies');
                    Route::get('/{currencyId}', [\App\Http\Controllers\Api\CurrencyController::class, 'show'])
                        ->name('read.currencies');
                    Route::put('/{currencyId}', [\App\Http\Controllers\Api\CurrencyController::class, 'update'])
                        ->name('update.currencies');
                    Route::delete('/{currencyId}', [\App\Http\Controllers\Api\CurrencyController::class, 'destroy'])
                        ->name('delete.currencies');
                });

                //* Admin routes for continents
                Route::prefix('continents')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\ContinentController::class, 'index'])
                        ->name('browse.continents');
                    Route::post('/', [\App\Http\Controllers\Api\ContinentController::class, 'store'])
                        ->name('create.continents');
                    Route::get('/{continentId}', [\App\Http\Controllers\Api\ContinentController::class, 'show'])
                        ->name('read.continents');
                    Route::put('/{continentId}', [\App\Http\Controllers\Api\ContinentController::class, 'update'])
                        ->name('update.continents');
                    Route::delete('/{continentId}', [\App\Http\Controllers\Api\ContinentController::class, 'destroy'])
                        ->name('delete.continents');
                });

                //* Admin routes for countries
                Route::prefix('countries')->group(function () {
                    //* Options
                    Route::get('/options/currencies', [\App\Http\Controllers\Api\CountryController::class, 'optionCurrencies'])
                        ->name('options.currencies.countries');
                    Route::get('/options/continents', [\App\Http\Controllers\Api\CountryController::class, 'optionContinents'])
                        ->name('options.continents.countries');

                    Route::get('/', [\App\Http\Controllers\Api\CountryController::class, 'index'])
                        ->name('browse.countries');
                    Route::post('/', [\App\Http\Controllers\Api\CountryController::class, 'store'])
                        ->name('create.countries');
                    Route::get('/{countryId}', [\App\Http\Controllers\Api\CountryController::class, 'show'])
                        ->name('read.countries');
                    Route::put('/{countryId}', [\App\Http\Controllers\Api\CountryController::class, 'update'])
                        ->name('update.countries');
                    Route::delete('/{countryId}', [\App\Http\Controllers\Api\CountryController::class, 'destroy'])
                        ->name('delete.countries');
                });

                //* Admin routes for languages
                Route::prefix('languages')->group(function () {
                    Route::get('/', [\App\Http\Controllers\Api\LanguageController::class, 'index'])
                        ->name('browse.languages');
                    Route::post('/', [\App\Http\Controllers\Api\LanguageController::class, 'store'])
                        ->name('create.languages');
                    Route::get('/{languageId}', [\App\Http\Controllers\Api\LanguageController::class, 'show'])
                        ->name('read.languages');
                    Route::put('/{languageId}', [\App\Http\Controllers\Api\LanguageController::class, 'update'])
                        ->name('update.languages');
                    Route::delete('/{languageId}', [\App\Http\Controllers\Api\LanguageController::class, 'destroy'])
                        ->name('delete.languages');
                });

                // Route::get('/tokens/banned', [TokenController::class, 'getBannedTokens'])->name('browse_token');
                // Route::post('/token/ban/{uuid}', [TokenController::class, 'banToken'])->name('create_token');
                // Route::delete('/token/unban', [TokenController::class, 'unbanToken'])->name('delete_token');

                // Route::get('/ratelimiter/status', [\App\Http\Controllers\Api\AuthController::class, 'getRateLimitStatus']);

                //* Cache management (Admin only)
                // Route::get('/cache/stats', [\App\Http\Controllers\Api\AuthController::class, 'getCacheStats']);
                // Route::post('/cache/warmup', [\App\Http\Controllers\Api\AuthController::class, 'warmUpCache']);
                // Route::delete('/cache/clear', [\App\Http\Controllers\Api\AuthController::class, 'clearUserCache']);
            });
    });
