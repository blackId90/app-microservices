<?php

namespace App\Enums;

enum PermissionTargetEnum: string {
    case SELF = '_self';
    case BLANK = '_blank';

    public function label(): string {
        return match ($this) {
            self::SELF => 'Self',
            self::BLANK => 'Blank',
        };
    }
}
