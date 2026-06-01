<?php

namespace App\Services\Applications;

use App\Repositories\Interfaces\LoginAttemptRepositoryInterface;

class LoginAttemptService {

    public function __construct(
        protected LoginAttemptRepositoryInterface $loginAttemptRepo
    ) {
    }

    public function listAll() {
        return $this->loginAttemptRepo->getAll();
    }

    public function createLog(array $credentials, string $textIdentifier, bool $status = false, ?string $userId = null) {
        $messageIdentifier = "{$textIdentifier}! Identifier: {$credentials['auth_user_email']}";

        $this->loginAttemptRepo->log($messageIdentifier, $status, $userId);
    }

    public function listByIdentifier(string $identifier) {
        return $this->loginAttemptRepo->getByIdentifier($identifier);
    }

    public function getLatestLogin(string $userId) {
        return $this->loginAttemptRepo->getLatest($userId);
    }
}
