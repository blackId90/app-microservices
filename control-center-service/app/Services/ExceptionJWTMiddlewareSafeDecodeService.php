<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class ExceptionJWTMiddlewareSafeDecodeService {
    protected string $algo;
    protected string $secret;
    protected string $issuer;

    /**
     * Create a new class instance.
     */
    public function __construct() {
        $this->algo   = env('JWT_ALGO', 'HS256');
        $this->secret = env('JWT_SECRET');
        $this->issuer = env('JWT_ISSUER');
    }

    public function safeDecode(?string $token): ?array {
        if (!is_string($token) || trim($token) === '' || substr_count($token, '.') !== 2) {
            Log::warning('Failed safe decode: format token invalid', ['token' => $token]);

            return null;
        }

        try {
            $decoded = (array) JWT::decode($token, new Key($this->secret, $this->algo));

            return $decoded;
        } catch (\Exception $e) {
            Log::warning('Failed safe decode: ' . $e->getMessage(), ['token' => $token]);

            return null;
        }
    }
}
