<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!app()->isProduction()) {
            DB::listen(function ($query) {
                $queryTime = round($query->time, 2);
                $rawSql = $query->toRawSql();

                $request = request();
                $totalTime = $request->attributes->get('profilingQueryTime', 0.0);
                $queries = $request->attributes->get('profilingQueryDetails', []);

                $queries[] = [
                    'raw_sql' => $rawSql,
                    'time_ms' => $queryTime,
                ];

                $request->attributes->set('profilingQueryTime', $totalTime + $queryTime);
                $request->attributes->set('profilingQueryDetails', $queries);
            });
        }
    }
}
