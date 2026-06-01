<?php

namespace App\Enums;

enum LoginAttemptTypeEnum: string {
    case ActionLogin = 'login';
    case ActionRefresh = 'refresh';
    case ActionLogout = 'logout';

    public function getMessage(): string {
        return match ($this) {
            self::ActionLogin => 'Login',
            self::ActionRefresh => 'Refresh token',
            self::ActionLogout => 'Logout'
        };
    }

    public static function resolve(string $pathname): ?LoginAttemptTypeEnum {
        return match ($pathname) {
            'api/v1/auth/login' => self::ActionLogin,
            'api/v1/auth/refresh' => self::ActionRefresh,
            'api/v1/auth/logout' => self::ActionLogout,
            default => null
        };
    }
}
