<?php

namespace App\Traits;

use App\Contracts\AppAuthEnumCodeContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
// use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Str;

trait ApiResponseFormatter {
    protected float $startTime;

    public function startProfiling(): void {
        $this->startTime = microtime(true);
    }

    public function formatResponse(int $status = 200, AppAuthEnumCodeContract|string $message = 'success', mixed $data = null, AppAuthEnumCodeContract|string|null $codeName = null, array $errors = []): JsonResponse {
        $isShowDebug = app()->environment(['local', 'development']);
        $response = [
            'status' => $status,
            'message' => $message,
        ];

        if ($status >= 200 && $status < 300) {
            $response['data'] = $this->formatData($data);
        } else {
            $req = request();
            $now = now();

            if (!empty($data))
                $response['data'] = $data;

            if (!empty($errors))
                $response['errors'] = $errors;

            $response['error'] = [
                'request_id' => $req->attributes->get('requestId'),
                'status_code' => $status,
                'code_name' => $codeName
            ];

            if ($isShowDebug) {
                $reqUserId = $req->attributes->get('userId');
                $reqRoleId = $req->attributes->get('roleId');

                $response['error']['meta_request'] = [
                    'method' => $req->method(),
                    'host' => $req->schemeAndHttpHost(), // ->httpHost(), ->getHttpHost()
                    'path' => $req->getPathInfo(), // $req->path(),
                    'route' => optional($req->route())->getName(),
                    'placeholders' => optional($req->route())->parameters(),
                    'user_id' => $reqUserId,
                    'role_id' => $reqRoleId, // $reqRoleId ?? ($reqUserId ? $this->userCache->getUser($reqUserId)?->auth_user_role_id ?? null : null),
                    'ip_address' => $req->ip(),
                    'user_agent' => $req->userAgent(),
                    'timestamp' => $now->toISOString(), // ->format('Y-m-d H:i:s.u')
                ];
            }
        }

        if ($isShowDebug) {
            $req = request();
            $reqUserId = $req->attributes->get('userId');
            $reqRoleId = $req->attributes->get('roleId');

            if (!gc_enable())
                gc_enable();

            //* Paksa pengumpulan siklus
            $gcStatus = gc_status();
            $collected = gc_collect_cycles();

            $memoryUsageKB = $this->toKilobytes(memory_get_usage(true));
            $peakMemoryKB = $this->toKilobytes(memory_get_peak_usage(true));

            $startTime = (float) $req->header('X-Profiling') ?? request()->attributes->get('profilingStart');
            $executionTime = $startTime ? $this->toMilliseconds(microtime(true) - $startTime) : null;

            $jwtAuthTime = request()->attributes->get('stopProfilingMiddlewareJwtAuthTime');
            $queryTimeMs = request()->attributes->get('profilingQueryTime');
            $queryDetails = request()->attributes->get('profilingQueryDetails') ?? [];

            //* Tampilkan info garbage collector
            $response['debug_info'] = [
                'mode' => app()->environment(),
                'total_execution_time_ms' => $executionTime,
                'middleware_jwt_execution_time_ms' => $jwtAuthTime,
                'query_execution_time_ms' => $queryTimeMs,
                'queries' => array_map(function ($q) {
                    return [
                        'raw_sql' => $q['raw_sql'],
                        'time_ms' => round($q['time_ms'], 2),
                    ];
                }, $queryDetails),
                'garbage_colector' => [
                    'collected_cycles' => $collected,
                    'status' => $gcStatus,
                    'memory_usage_kb' => $memoryUsageKB,
                    'peak_memory_usage_kb' => $peakMemoryKB,
                ]
            ];
        }

        return response()->json($response, $status);
    }

    private function formatData($data) {
        if ($data instanceof LengthAwarePaginator) {
            return [
                'records' => $data->items(),
                'pagination' => [
                    'total' => $data->total(),
                    'per_page' => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage()
                ]
            ];
        }

        return $data;
    }

    protected function toKilobytes(float $bytes): float {
        return round($bytes / 1024, 2);
    }

    protected function toMilliseconds(float $seconds): float {
        return round($seconds * 1000, 2);
    }
}
