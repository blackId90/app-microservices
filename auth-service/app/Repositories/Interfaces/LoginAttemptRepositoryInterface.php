<?php

namespace App\Repositories\Interfaces;

use App\Models\LoginAttempt;

interface LoginAttemptRepositoryInterface {

    public function getAll();

    public function getByIdentifier(string $identifier);

    public function log(string $identifier, bool $status = false, ?string $userId = null): void;

    public function getLatest(string $userId): ?LoginAttempt;
}
