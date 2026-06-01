<?php

namespace App\Exceptions;

use App\Contracts\AppAuthEnumCodeContract;
use App\Traits\HasErrorCode;
use App\Traits\LogAudit;
use Exception;

class AppControlCenterException extends Exception {
    use HasErrorCode, LogAudit;

    protected string $codeName;
    protected int $status;
    protected array $context;

    public function __construct(AppAuthEnumCodeContract $codeName, ?int $status = 500, ?array $context = []) {
        $statusCode = $codeName?->getStatusCode() ?? $status;
        parent::__construct($codeName->value, $statusCode);

        $this->codeName = $codeName->value;
        $this->status = $statusCode;
        $this->context = $context;

        //* Dynamic logging on construct
        $this->handleDynamicLogging();
    }

    /**
     * Handle dynamic logging based on context configuration
     */
    protected function handleDynamicLogging(): void {
        $logContext = $this->getContextLog();
        if (empty($logContext))
            return;

        //* Check if logging is enabled (default false)
        if ($this->shouldLogContext())
            LogAudit::logAudit($this, $logContext['additionalLog']['level'], $logContext['additionalLog']['message'], $logContext['additionalLog']['extraContextLog']);

        //* Check if discord notify is enabled (default false)
        if ($this->shouldNotifyContext())
            LogAudit::notifyAudit($this, $logContext['additionalLog']['level'], $logContext['additionalLog']['message']);
    }
}
