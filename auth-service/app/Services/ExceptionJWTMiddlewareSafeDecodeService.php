<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class ExceptionJWTMiddlewareSafeDecodeService {
    /**
     * Create a new class instance.
     */
    public function __construct() {
        //
    }

    public function safeDecode(?string $token): ?array {
        if (!is_string($token) || trim($token) === '' || substr_count($token, '.') !== 2) {
            Log::warning('Failed safe decode: format token invalid', ['token' => $token]);

            return null;
        }

        try {
            return JWTAuth::getJWTProvider()->decode($token);
        } catch (\Exception $e) {
            Log::warning('Failed safe decode: ' . $e->getMessage(), ['token' => $token]);

            return null;
        }
    }
}
