<?php

namespace App\Services\Clients;

use App\Services\BaseClientService;
use Exception;
use Illuminate\Support\Facades\{App, Log};
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuthPermissionClient extends BaseClientService {
    private bool $configured = false;
    private string $serviceMessage = 'Auth Service';

    /**
     * Create a new class instance.
     */
    public function __construct() {
        parent::__construct([]);
    }

    /**
     * Get list token management data service auth
     *
     * @param array $queryParams
     * @return array
     */
    public function getListAuthPermission(array $queryParams = []): array {
        $this->ensureConfigured();

        try {
            return $this->get('/auth_permissions', $queryParams) ?? $this->fallbackResponse();
        } catch (Exception $e) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResponse();
        }
    }

    /**
     * Store authentication role data service auth
     *
     * @param array $payloads
     * @return array
     */
    public function storeAuthPermission(array $payloads): array {
        $this->ensureConfigured();

        try {
            return $this->post('/auth_permissions', $payloads) ?? $this->fallbackResponse();
        } catch (Exception $ex) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $ex->getMessage(),
            ]);

            return $this->fallbackResponse($ex->getMessage());
        }
    }

    /**
     * Detail authentication role data service auth
     *
     * @param string $authPermissionId
     * @param array $queryParams
     * @return array
     */
    public function getAuthPermission(string $authPermissionId, array $queryParams = []): array {
        $this->ensureConfigured();

        try {
            return $this->get("/auth_permissions/{$authPermissionId}", $queryParams) ?? $this->fallbackResponse();
        } catch (Exception $e) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResponse();
        }
    }

    /**
     * Update authentication role data service auth
     *
     * @param string $authPermissionId
     * @param array $queryParams
     * @param array $payloads
     * @return array
     */
    public function updateAuthPermission(string $authPermissionId, array $payloads, array $queryParams = []): array {
        $this->ensureConfigured();

        try {
            return $this->put("/auth_permissions/{$authPermissionId}", $payloads, $queryParams) ?? $this->fallbackResponse();
        } catch (Exception $ex) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $ex->getMessage(),
            ]);

            return $this->fallbackResponse($ex->getMessage());
        }
    }

    /**
     * Delete authentication role data service auth
     *
     * @param string $authPermissionId
     * @param array $queryParams
     * @return array
     */
    public function destroyAuthPermission(string $authPermissionId, array $queryParams = []): array {
        $this->ensureConfigured();

        try {
            return $this->delete(uri: "/auth_permissions/{$authPermissionId}", query: $queryParams) ?? $this->fallbackResponse();
        } catch (Exception $ex) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $ex->getMessage(),
            ]);

            return $this->fallbackResponse($ex->getMessage());
        }
    }

    public function getOptionsAuthPermission(array $queryParams = []): array {
        $this->ensureConfigured();

        try {
            return $this->get('/auth_permissions/options/permissions', $queryParams) ?? $this->fallbackResponse();
        } catch (Exception $e) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResponse();
        }
    }

    /**
     * Fallback responses
     */
    private function fallbackResponse(string $error = 'Service is currently unavailable'): array {
        return [
            'success' => false,
            'error' => 'Service temporarily unavailable',
            'fallback' => true,
            'data' => [
                'service' => $this->getConfig('service_name'),
                'message' => $error, // 'Service is currently unavailable',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Lazy configuration on first use
     */
    private function ensureConfigured(): void {
        if (!$this->configured) {
            $this->loadConfiguration();
            $this->configured = true;
        }
    }

    /**
     * Load configuration from config/services.php
     */
    private function loadConfiguration(): void {
        $apiKey = config('services.application.api_key', null);
        $config = config('services.application.auth_service', []);

        $serviceConfig = [
            'base_url' => $config['url'] ?? null,
            'x_api_key' => $apiKey,
            'timeout' => $config['timeout'] ?? 5,
            'retries' => $config['retries'] ?? 3,
            'retry_delay' => $config['retry_delay'] ?? 200,
            'service_name' => $config['service_name'] ?? 'AuthService',
            'default_headers' => [
                'X-Request-Source' => Str::studly(config('app.name')),
                'X-Client-Version' => '1.0.0',
            ],
        ];

        //* Get token from current request if available
        if (App::has('request') && $token = App::make(Request::class)->bearerToken())
            $serviceConfig['token'] = $token;

        $this->configure($serviceConfig);
    }
}
