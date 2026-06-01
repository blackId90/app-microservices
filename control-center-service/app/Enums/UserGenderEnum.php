<?php

namespace App\Enums;

enum UserGenderEnum: string {
    case MALE = 'male';
    case FEMALE = 'female';

    public function label(): string {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female'
        };
    }

    public function salutation(): string {
        return match ($this) {
            self::MALE => 'Mr.',
            self::FEMALE => 'Ms.'
        };
    }
}
