<?php

namespace App\Services\Clients;

use App\Services\BaseClientService;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TokenManagementClient extends BaseClientService {
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
     * @return array
     */
    public function getListTokenManagement(): array {
        $this->ensureConfigured();

        try {
            return $this->get('/tokens/banned') ?? $this->fallbackResponse();
        } catch (Exception $e) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResponse();
        }
    }

    /**
     * Store banned token management data service auth
     *
     * @param array $data
     * @return array
     */
    public function storeBannedTokenManagement(string $authUserId): array {
        $this->ensureConfigured();

        try {
            return $this->post("/tokens/banned/{$authUserId}") ?? $this->fallbackResponse();
        } catch (Exception $ex) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $ex->getMessage(),
            ]);

            return $this->fallbackResponse($ex->getMessage());
        }
    }

    /**
     * Destroy banned token management data service auth
     *
     * @param array $data
     * @return array
     */
    public function destroyUnbannedTokenManagement(string $authUserId): array {
        $this->ensureConfigured();

        try {
            return $this->delete("/tokens/banned/{$authUserId}") ?? $this->fallbackResponse();
        } catch (Exception $ex) {
            Log::error("{$this->serviceMessage} error", [
                'service' => $this->getConfig('service_name'),
                'error' => $ex->getMessage(),
            ]);

            return $this->fallbackResponse($ex->getMessage());
        }
    }

    /**
     * Update token from current request
     */
    public function updateTokenFromRequest(): self {
        if (App::has('request') && $token = App::make(Request::class)->bearerToken())
            $this->setToken($token);

        return $this;
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
