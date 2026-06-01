<?php

namespace App\Services\Clients;

use App\Services\BaseClientService;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ControlCenterServiceClient extends BaseClientService {
    private bool $configured = false;

    /**
     * Constructor - hanya inisialisasi dasar
     */
    public function __construct() {
        //* Kosongkan constructor parent, kita akan configure nanti
        parent::__construct([]);
    }

    /**
     * Check service health
     */
    public function getServiceApi(): array {
        $this->ensureConfigured();

        try {
            return $this->get('/public') ?? $this->healthFallback();
        } catch (\Exception $e) {
            return $this->healthFallback($e->getMessage());
        }
    }

    /**
     * Check service health
     */
    public function getServiceHealth(): array {
        $this->ensureConfigured();

        try {
            return $this->get('/public/health') ?? $this->healthFallback();
        } catch (\Exception $e) {
            return $this->healthFallback($e->getMessage());
        }
    }

    /**
     * Create company and user in Control Center Service
     *
     * @param array $data Company and user data
     * @return array
     */
    public function createCompanyWithUser(array $data): array {
        $this->ensureConfigured();

        try {
            return $this->post('/public/register', $data) ?? $this->companyFallback();
        } catch (\Exception $e) {
            return $this->companyFallback($e->getMessage());
        }
    }

    public function verifyEmailCompany(string $companyId, $keyEmail): array {
        $this->ensureConfigured();

        try {
            return $this->get("/public/email/verify/{$companyId}/{$keyEmail}") ?? $this->companyFallback();
        } catch (\Exception $ex) {
            return $this->companyFallback($ex->getMessage());
        }
    }

    public function destroyCompanyWithUser(string $companyId, string $userAuthUserId): array {
        $this->ensureConfigured();

        try {
            return $this->delete("/public/register/{$companyId}/{$userAuthUserId}") ?? $this->companyFallback();
        } catch (\Exception $e) {
            return $this->companyFallback($e->getMessage());
        }
    }

    private function healthFallback(string $error = 'Service unavailable'): array {
        return [
            'status' => 'down',
            'message' => $error,
            'timestamp' => now()->toISOString(),
        ];
    }

    private function companyFallback(string $error = 'Service is currently unavailable'): array {
        return [
            'success' => false,
            'error' => 'Service temporarily unavailable',
            'fallback' => true,
            'data' => [
                'service' => $this->getConfig('service_name'),
                'message' => $error,
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
        $config = config('services.application.control_center_service', []);

        $serviceConfig = [
            'base_url' => $config['url'] ?? null,
            'x_api_key' => $apiKey,
            'timeout' => $config['timeout'] ?? 5,
            'retries' => $config['retries'] ?? 3,
            'retry_delay' => $config['retry_delay'] ?? 200,
            'service_name' => $config['service_name'] ?? 'ControlCenterService',
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
