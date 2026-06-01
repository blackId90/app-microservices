<?php

namespace App\Enums;

enum PaymentsMethodsEnum: string {
    case CASH = 'cash';
    case CREDIT_CARD = 'credit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case VIRTUAL_ACCOUNT = 'virtual_account';
    case EWALLET = 'ewallet';
    case PAYLATER = 'paylater';

    public function label(): string {
        return match ($this) {
            self::CASH => 'Cash',
            self::CREDIT_CARD => 'Credit Card',
            self::BANK_TRANSFER => 'Bank Transfer',
            self::VIRTUAL_ACCOUNT => 'Virtual Account',
            self::EWALLET => 'E-Wallet',
            self::PAYLATER => 'PayLater'
        };
    }
}
