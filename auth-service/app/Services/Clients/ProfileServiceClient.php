<?php

namespace App\Services\Clients;

use App\Services\BaseClientService;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class ProfileServiceClient extends BaseClientService {
    private bool $configured = false;
    private ?bool $isTempToken = null;
    private ?string $manualToken = null;
    // private bool $resetRequested = false;
    private bool $isLogDebug = false;

    /**
     * Constructor - hanya inisialisasi dasar
     */
    public function __construct() {
        parent::__construct([]);
    }

    /**
     * Get profile data service control center
     *
     * @return array
     */
    public function getProfileDataControlCenter(): array {
        $this->ensureConfigured();

        try {
            return $this->get('/auth-user') ?? $this->fallbackResponse();
        } catch (Exception $e) {
            //! Log
            Log::error("Control center service error", [
                'service' => $this->getConfig('service_name'),
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResponse();
        }
    }

    /**
     * Destroy profile data service control center
     *
     * @return mixed
     */
    public function destroyProfileDataControlCenter(): mixed {
        $this->ensureConfigured();

        try {
            return $this->delete('/auth-user') ?? $this->fallbackResponse();
        } catch (Exception $e) {
            //! Log
            Log::error("Control center service error", [
                'service' => $this->getConfig('service_name'),
                'error' => $e->getMessage(),
            ]);

            return $this->fallbackResponse();
        }
    }

    /**
     * Reset and set new token (for login process)
     */
    public function resetWithToken(string $token): self {
        //! Log
        if ($this->isLogDebug) {
            Log::debug('ProfileServiceClient resetting with new token', [
                'token_preview' => substr($token, 0, 30) . '...',
                'previous_configured' => $this->configured,
            ]);
        }

        //* Reset semua state
        $this->reset();

        //* Set manual token
        $this->manualToken = $token;

        //* Force reconfigure
        $this->configured = false;
        $this->client = null;

        //* Detect token type
        $this->detectTokenType();

        return $this;
    }

    /**
     * Reset client state
     */
    public function reset(): self {
        $this->configured = false;
        $this->manualToken = null;
        $this->isTempToken = null;
        $this->client = null;
        $this->config = []; // Reset config too

        //! Log
        if ($this->isLogDebug)
            Log::debug('ProfileServiceClient reset complete');

        return $this;
    }

    /**
     * Update token from current request (from regular requests)
     */
    public function updateTokenFromRequest(): self {
        /*
        if (App::has('request') && $token = App::make(Request::class)->bearerToken())
            $this->setToken($token);

        return $this;
        */

        if ($this->manualToken) {
            //! Log
            if ($this->isLogDebug)
                Log::debug('ProfileServiceClient has manual token, skipping request token');

            //* If there is already a manual token (from login), do not overwrite it.
            return $this;
        }

        if (App::has('request') && $token = App::make(Request::class)->bearerToken()) {
            //! Log
            if ($this->isLogDebug)
                Log::debug('ProfileServiceClient updating token from request');

            $this->manualToken = $token;
            $this->detectTokenType();

            //* Force reconfigure jika sudah configured sebelumnya
            if ($this->configured) {
                $this->configured = false;
                $this->client = null;
            }
        } else {
            //! Log
            Log::warning('ProfileServiceClient no token in request');
        }

        return $this;
    }

    /**
     * Fallback responses
     */
    private function fallbackResponse(): array {
        return [
            'success' => false,
            'error' => 'Service temporarily unavailable',
            'fallback' => true,
            'data' => [
                'service' => $this->getConfig('service_name'),
                'message' => 'Service is currently unavailable',
                'timestamp' => now()->toISOString(),
            ],
        ];
    }

    /**
     * Detect whether the token is a temp token
     */
    private function detectTokenType(): void {
        $token = $this->manualToken;
        if (!$token) {
            $this->isTempToken = false;

            return;
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();

            /**
             * PERBAIKAN: Gunakan get() bukan has()
             * has() membutuhkan Claim object, sedangkan get() menerima string
             */
            $this->isTempToken = (bool) $payload->get('temp', false);

            //* Debug: cek semua claims untuk memastikan
            $allClaims = $payload->toArray();

            //! Log
            if ($this->isLogDebug) {
                Log::debug('Token type detected', [
                    'is_temp_token' => $this->isTempToken,
                    'temp_value' => $payload->get('temp'),
                    'scope' => $payload->get('scope', 'none'),
                    'purpose' => $payload->get('purpose', 'none'),
                    'all_claims_keys' => array_keys($allClaims),
                ]);
            }
        } catch (JWTException $e) {
            $this->isTempToken = false;

            //! Log
            Log::error('Failed to detect token type', [
                'error' => $e->getMessage(),
                'token_preview' => substr($token, 0, 30) . '...',
            ]);
        }
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

        if ($this->manualToken) {
            //* Prioritize manual tokens if any
            $serviceConfig['token'] = $this->manualToken;

            //! Log
            if ($this->isLogDebug) {
                Log::debug('ProfileServiceClient configured with manual token', [
                    'token_preview' => substr($this->manualToken, 0, 30) . '...',
                ]);
            }
        } elseif (App::has('request') && $token = App::make(Request::class)->bearerToken()) {
            //* Fallback to token from request
            $serviceConfig['token'] = $token;
            $this->manualToken = $token;

            //! Log
            if ($this->isLogDebug)
                Log::debug('ProfileServiceClient configured with request token');
        } else {
            //! Log
            Log::warning('ProfileServiceClient configured WITHOUT token');
        }

        $this->configure($serviceConfig);

        //! Log
        if ($this->isLogDebug) {
            Log::debug('ProfileServiceClient configuration loaded', [
                'has_token' => $this->hasConfig('token'),
                'base_url' => $this->getConfig('base_url'),
            ]);
        }
    }
}
