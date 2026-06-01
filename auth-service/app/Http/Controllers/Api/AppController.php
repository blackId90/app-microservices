<?php

namespace App\Http\Controllers\Api;

use App\Enums\AppAuthResponseCode;
use App\Http\Controllers\RestController;
use App\Services\Clients\ControlCenterServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\IpUtils;

class AppController extends RestController {

    public function __construct(
        protected ControlCenterServiceClient $controlCenterServiceClient
    ) {
    }

    public function apiAuthService(): JsonResponse {
        $data = [
            'status' => 'Success Connect API'
        ];

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    public function healthAuthService(): JsonResponse {
        try {
            $checks = [];
            $errors = [];

            //* Check API
            $checks['api'] = 'OK';

            //* Check database
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

            //* Check Cache
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

            //* Check Storage
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

            //* Check Queue
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

    public function debugIpAuthService(Request $request): JsonResponse {
        $data = [
            'client_ip' => $request->ip(),
            'is_secure' => $request->secure(),
            'all_headers' => $request->headers->all(),
        ];

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    public function debugProxyAuthService(Request $request): JsonResponse {
        $remoteAddr = $_SERVER['REMOTE_ADDR']; // IP asli yang memukul server Laravel (Harusnya IP Kong)
        $laravelIp = $request->ip(); // IP yang dianggap Laravel sebagai Client (Harusnya IP Anda/User)

        $trustedProxies = Request::getTrustedProxies();

        //* Cek apakah IP Kong sudah masuk daftar trusted
        $isTrusted = $trustedProxies && IpUtils::checkIp($remoteAddr, $trustedProxies);

        $data = [
            'status' => $isTrusted ? 'SUCCESS' : 'WARNING',
            'message' => $isTrusted ? 'Konfigurasi Benar! Laravel mempercayai Kong.' : 'Konfigurasi Salah! Laravel menganggap Kong adalah User.',
            'details' => [
                'kong_server_ip' => $remoteAddr,
                'detected_client_ip' => $laravelIp,
                'is_secure_https' => $request->secure(),
                'trusted_proxies' => $trustedProxies,
                'trusted_proxies_list' => config('trustedproxy.proxies') ?? 'Check bootstrap/app.php',
            ],
            'headers_received' => [
                'host' => $request->header('Host'),
                'detected_host' => $request->getHost(),
                'x-forwarded-for' => $request->header('X-Forwarded-For'),
                'x-forwarded-proto' => $request->header('X-Forwarded-Proto'),
            ]
        ];

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $data
        );
    }

    public function apiControlCenterService(): JsonResponse {
        $health = $this->controlCenterServiceClient->getServiceApi();

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $health
        );
    }

    public function healthControlCenterService(): JsonResponse {
        $health = $this->controlCenterServiceClient->getServiceHealth();

        return $this->formatResponse(
            message: AppAuthResponseCode::SuccessRetrieveData->getMessage('success'),
            data: $health
        );
    }
}
