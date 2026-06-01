<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\RestController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class HealthController extends RestController {
    /**
     * Display a listing of the resource.
     */
    public function index() {
        $service = config('app.name');
        $status = 'Success Connect API';

        return $this->formatResponse(message: "{$status} {$service}");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function health() {
        try {
            $checks = [];
            $errors = [];

            // ✅ API check
            $checks['api'] = 'OK';
            $checks['service'] = config('app.name');
            $checks['timestamp'] = now()->toISOString();

            // ✅ Database check
            try {
                DB::select('SELECT 1 as check_connection');
                $checks['database'] = [
                    'driver' => config('database.default'),
                    'database' => DB::connection()->getDatabaseName(),
                    'status' => 'OK'
                ];
            } catch (\Exception $e) {
                $checks['database'] = [
                    'driver' => config('database.default'),
                    'status' => 'FAIL',
                ];
                $errors['database'] = $e->getMessage();
            }

            // ✅ Cache check
            try {
                Cache::put('health_check', 'ok', 5);
                $value = Cache::get('health_check');
                $checks['cache'] = [
                    'driver' => config('cache.default'),
                    'status' => $value === 'ok' ? 'OK' : 'FAIL',
                ];

                if ($value !== 'ok')
                    $errors['cache'] = 'Cache value mismatch';
            } catch (\Exception $e) {
                $checks['cache'] = [
                    'driver' => config('cache.default'),
                    'status' => 'FAIL',
                ];
                $errors['cache'] = $e->getMessage();
            }

            // ✅ Storage check
            try {
                $testFile = 'health_check.txt';
                Storage::disk('local')->put($testFile, 'ok');
                $content = Storage::disk('local')->get($testFile);
                $checks['storage'] = [
                    'driver' => config('filesystems.default'),
                    'status' => $content === 'ok' ? 'OK' : 'FAIL',
                ];

                if ($content !== 'ok')
                    $errors['storage'] = 'Storage read/write mismatch';

                Storage::disk('local')->delete($testFile);
            } catch (\Exception $e) {
                $checks['storage'] = [
                    'driver' => config('filesystems.default'),
                    'status' => 'FAIL',
                ];
                $errors['storage'] = $e->getMessage();
            }

            // ✅ Queue check
            try {
                $queueDriver = config('queue.default');
                $checks['queue'] = [
                    'driver' => $queueDriver,
                    'status' => 'OK'
                ];
            } catch (\Exception $e) {
                $checks['queue'] = [
                    'driver' => config('queue.default'),
                    'status' => 'FAIL',
                ];
                $errors['queue'] = $e->getMessage();
            }

            $hasFail = !empty($errors);

            return $this->formatResponse(
                $hasFail ? 500 : 200,
                $hasFail ? 'Some services are failing' : 'All systems operational',
                $checks,
                $hasFail ? 'SERVICE_CHECK_FAILED' : null,
                $errors
            );
        } catch (\Exception $e) {
            return $this->formatResponse(500, 'Database connection failed', null, 'DB_CONNECTION_ERROR', [$e->getMessage()]);
        }
    }
}
