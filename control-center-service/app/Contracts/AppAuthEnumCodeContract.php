<?php

namespace App\Contracts;

interface AppAuthEnumCodeContract {
    public function getMessage(): string;

    public function getStatusCode(): int;

    // public function value(): string;
}
