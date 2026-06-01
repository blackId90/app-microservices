<?php

namespace App\Providers;

use App\Models\AuthUser;
use App\Observers\AuthUserObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider {
    /**
     * Register any application services.
     */
    public function register(): void {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void {
        if (!app()->isProduction()) {
            DB::listen(function ($query) {
                $queryTime = round($query->time, 2);
                $rawSql = $query->toRawSql();

                /*
                $bindings = collect($query->bindings)
                    ->map(fn($b) => isString($b) ? "'{$b}'" : (isNull($b) ? 'NULL' : $b))
                    ->implode(', ');

                $time = number_format($query->time, 2);

                $bindingsJSON = json_encode($query->bindings, JSON_PRETTY_PRINT);
                $log = <<<EOL
                📦 SQL Executed:
                🔹 Query   : {$query->sql}
                🔹 Raw SQL : {$query->toRawSql()}
                🔹 Bindings: {$bindingsJSON}
                🔹 Time    : {$time} ms
                EOL;
                */

                /*
                $time = number_format($query->time, 2);
                $log = <<<EOL
                📦 SQL Executed in {$time} ms:
                🔹 Raw SQL : {$query->toRawSql()}
                EOL;

                Log::info($log);
                */

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

        //* Register observer for auto-caching
        AuthUser::observe(AuthUserObserver::class);
    }
}
