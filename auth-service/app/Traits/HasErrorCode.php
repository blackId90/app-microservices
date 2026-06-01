<?php

namespace App\Traits;

use App\Enums\AppAuthResponseCode;

trait HasErrorCode {
    public function getErrorEnum(): ?AppAuthResponseCode {
        return AppAuthResponseCode::resolve($this->getCodeName());
    }

    public function getCodeName(): string {
        // return $this->codeName ?? 'unexpected_error';
        return property_exists($this, 'codeName') && isset($this->codeName) ? $this->codeName : 'unexpected_error';
    }

    public function getErrorMessage(): string {
        if ($this->getErrorEnum()?->getMessage())
            return $this->getErrorEnum()->getMessage();

        return method_exists($this, 'getMessage') && is_callable([$this, 'getMessage']) ? $this->getMessage() : 'Unexpected error';
    }

    public function getStatusCode(): int {
        return property_exists($this, 'status') ? $this->status : 500;
    }

    public function getContext(): array {
        return property_exists($this, 'context') && is_array($this->context) ? $this->context : [];
    }

    public function getContextLog(): array {
        $context = $this->getContext();
        if (!empty($context) && isset($context['log']))
            return $context['log'];

        return [];
    }

    public function getContextLogCodeName(): ?AppAuthResponseCode {
        $context = $this->getContextLog();
        if (!empty($context) && isset($context['internal']) && isset($context['internal']['codeName']))
            return $context['internal']['codeName'];

        return null;
    }

    /**
     * Check if context should logging
     */
    public function shouldLogContext(): bool {
        $contextLog = $this->getContextLog();
        if (!empty($contextLog) && isset($contextLog['additionalLog']) && isset($contextLog['additionalLog']['isLog']))
            return $contextLog['additionalLog']['isLog'];

        return false;
    }

    /**
     * Check if context should notify Discord
     */
    public function shouldNotifyContext(): bool {
        $contextLog = $this->getContextLog();
        if (!empty($contextLog) && isset($contextLog['additionalLog']) && isset($contextLog['additionalLog']['isNotify']))
            return $contextLog['additionalLog']['isNotify'];

        return false;
    }
}
