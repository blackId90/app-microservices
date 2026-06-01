<?php

use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request;

Route::prefix('v1/auth')
    // ->middleware(['auth:sanctum', 'throttle:api'])
    // ->middleware(['debug.profiling', 'assign.request', 'assign.locale'])
    ->group(function () {
        /*
        Route::get('/user', function (Request $request) {
            return $request->user();
        })->middleware('auth:sanctum');

        Route::get('/', function () {
            return response()->json(['message' => 'Success Connect']);
        });

        Route::apiResource('/', \App\Http\Controllers\Api\AppController::class)->only(['index']);
        */

        //* Public routes for development
        Route::prefix('dev')
            ->group(function () {
                //* Auth Service
                Route::get('/', [\App\Http\Controllers\Api\AppController::class, 'apiAuthService'])
                    ->name('read.checkApiService');
                Route::get('/health', [\App\Http\Controllers\Api\AppController::class, 'healthAuthService'])
                    ->name('read.checkHealthService');
                Route::get('/debug-ip', [\App\Http\Controllers\Api\AppController::class, 'debugIpAuthService'])
                    ->name('read.debugIp');
                Route::get('/debug-proxy', [\App\Http\Controllers\Api\AppController::class, 'debugProxyAuthService'])
                    ->name('read.debugProxy');

                //* Control Center Service
                Route::get('/api/control-center', [\App\Http\Controllers\Api\AppController::class, 'apiControlCenterService'])
                    ->name('read.checkApiControlCenterService');
                Route::get('/health/control-center', [\App\Http\Controllers\Api\AppController::class, 'healthControlCenterService'])
                    ->name('read.checkHealthControlCenterService');
            });

        Route::post('/register', [\App\Http\Controllers\Api\AuthController::class, 'register'])
            ->name('register');
        Route::get('/email/verify/{type}/{id}/{hash}', [\App\Http\Controllers\Api\AuthController::class, 'verify'])
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');
        Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login'])
            ->name('login');

        //* Protected routes with JWT Redis middleware
        Route::middleware('jwt')->group(function () {
            Route::get('/user', [\App\Http\Controllers\Api\AuthController::class, 'profile'])
                ->name('read.signinProfile');
            Route::get('/menus', [\App\Http\Controllers\Api\AuthController::class, 'menus'])
                ->name('read.signinMenus');
            Route::delete('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])
                ->name('delete.signinProfile');

            //* Internal routes with prefix internal (Communication from Services)
            Route::prefix('internal')
                ->middleware('api.key')
                // ->name('users_')
                ->group(function () {
                    Route::get('/menus', [\App\Http\Controllers\Api\AuthController::class, 'menus'])
                        ->name('read.internalSigninMenus');
                    Route::post('/has-access', [\App\Http\Controllers\Api\AuthController::class, 'checkAccessPermission'])
                        ->name('read.internalPermissionsCheck');

                    //* Admin routes for token managements
                    Route::get('/tokens/banned', [\App\Http\Controllers\Api\TokenController::class, 'getBannedTokens'])
                        ->name('browse.token');
                    Route::post('/tokens/banned/{authUserId}', [\App\Http\Controllers\Api\TokenController::class, 'banToken'])
                        ->name('create.token');
                    Route::delete('/tokens/banned/{authUserId}', [\App\Http\Controllers\Api\TokenController::class, 'unbanToken'])
                        ->name('delete.token');

                    //* Admin routes for auth roles
                    Route::prefix('auth_roles')->group(function () {
                        //* Options
                        Route::get('/options/permissions', [\App\Http\Controllers\Api\AuthRoleController::class, 'optionPermissions'])
                            ->name('options.permissions.auth_roles');

                        Route::get('/', [\App\Http\Controllers\Api\AuthRoleController::class, 'index'])
                            ->name('browse.auth_roles');
                        Route::post('/', [\App\Http\Controllers\Api\AuthRoleController::class, 'store'])
                            ->name('create.auth_roles');
                        Route::get('/{authRoleId}', [\App\Http\Controllers\Api\AuthRoleController::class, 'show'])
                            ->name('read.auth_roles');
                        Route::put('/{authRoleId}', [\App\Http\Controllers\Api\AuthRoleController::class, 'update'])
                            ->name('update.auth_roles');
                        Route::delete('/{authRoleId}', [\App\Http\Controllers\Api\AuthRoleController::class, 'destroy'])
                            ->name('delete.auth_roles');
                    });

                    //* Admin routes for auth permissions
                    Route::prefix('auth_permissions')->group(function () {
                        //* Options
                        Route::get('/options/permissions', [\App\Http\Controllers\Api\AuthPermissionController::class, 'optionPermissions'])
                            ->name('options.permissions.auth_permissions');

                        Route::get('/', [\App\Http\Controllers\Api\AuthPermissionController::class, 'index'])
                            ->name('browse.auth_permissions');
                        Route::post('/', [\App\Http\Controllers\Api\AuthPermissionController::class, 'store'])
                            ->name('create.auth_permissions');
                        Route::get('/{authPermissionId}', [\App\Http\Controllers\Api\AuthPermissionController::class, 'show'])
                            ->name('read.auth_permissions');
                        Route::put('/{authPermissionId}', [\App\Http\Controllers\Api\AuthPermissionController::class, 'update'])
                            ->name('update.auth_permissions');
                        Route::delete('/{authPermissionId}', [\App\Http\Controllers\Api\AuthPermissionController::class, 'destroy'])
                            ->name('delete.auth_permissions');
                    });

                    Route::prefix('auth_users')->group(function () {
                        //* Options
                        Route::get('/options/roles', [\App\Http\Controllers\Api\AuthUserController::class, 'optionRoles'])
                            ->name('options.roles.auth_users');

                        Route::post('/', [\App\Http\Controllers\Api\AuthUserController::class, 'store'])
                            ->name('create.auth_users');
                        Route::get('/{authUserId}', [\App\Http\Controllers\Api\AuthUserController::class, 'show'])
                            ->name('read.auth_users');
                        Route::put('/{authUserId}', [\App\Http\Controllers\Api\AuthUserController::class, 'update'])
                            ->name('update.auth_users');
                        Route::delete('/{authUserId}', [\App\Http\Controllers\Api\AuthUserController::class, 'destroy'])
                            ->name('delete.auth_users');
                    });
                    /*
                    Route::get('/auth-permissions', [\App\Http\Controllers\Api\AuthPermissionController::class, 'getAllPermissionsUser'])
                        ->name('read.authPermissions');
                    */
                });

            // Route::get('/ratelimiter/status', [\App\Http\Controllers\Api\AuthController::class, 'getRateLimitStatus']);

            //* Cache management (Admin only)
            // Route::get('/cache/stats', [\App\Http\Controllers\Api\AuthController::class, 'getCacheStats']);
            // Route::post('/cache/warmup', [\App\Http\Controllers\Api\AuthController::class, 'warmUpCache']);
            // Route::delete('/cache/clear', [\App\Http\Controllers\Api\AuthController::class, 'clearUserCache']);
        });
    });
