<?php

namespace App\Enums;

enum PermissionTypeEnum: string {
    case HEADER = 'header';
    case GROUP = 'group';
    case PARENT = 'parent';
    case ITEM = 'item';

    public function label(): string {
        return match ($this) {
            self::HEADER => 'Header',
            self::GROUP => 'Group',
            self::PARENT => 'Parent',
            self::ITEM => 'Item',
        };
    }
}
