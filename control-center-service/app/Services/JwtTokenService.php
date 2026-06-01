<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use UnexpectedValueException;
use App\Exceptions\JWTAuthException;
use App\Enums\AppAuthResponseCode;

class JwtTokenService {

    protected string $algo;
    protected string $secret;
    protected string $issuer;

    public function __construct() {
        $this->algo   = config('jwt.algo', 'HS256');
        $this->secret = config('jwt.secret');
        $this->issuer = config('jwt.issuer_claims');
    }

    /**
     * Decode dan validasi JWT
     */
    public function decode(string $token): array {
        try {
            $decoded = (array) JWT::decode($token, new Key($this->secret, $this->algo));

            //* Validasi issuer
            if (($decoded['iss'] ?? null) !== $this->issuer)
                throw new JWTAuthException(AppAuthResponseCode::InvalidToken);

            //* Validasi expiry
            if (($decoded['exp'] ?? 0) < time())
                throw new JWTAuthException(AppAuthResponseCode::ExpiredToken);

            return $decoded;
        } catch (JWTAuthException $e) {
            throw $e;
        } catch (UnexpectedValueException $e) {
            throw new JWTAuthException(AppAuthResponseCode::InvalidToken);
        }
    }
}
