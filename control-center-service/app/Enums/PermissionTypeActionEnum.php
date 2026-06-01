<?php

namespace App\Enums;

enum PermissionTypeActionEnum: string {
    case MODULE = 'module';
    case BROWSE = 'browse';
    case CREATE = 'create';
    case READ = 'read';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case IMPORT = 'import';
    case EXPORT = 'export';

    public function label(): string {
        return match ($this) {
            self::MODULE => 'Module',
            self::BROWSE => 'Browse',
            self::CREATE => 'Create',
            self::READ => 'Read',
            self::UPDATE => 'Update',
            self::DELETE => 'Delete',
            self::IMPORT => 'Import',
            self::EXPORT => 'Export',
        };
    }

    public static function resolveFromRouteName(string $routeName): ?PermissionTypeActionEnum {
        $action = explode('_', $routeName)[0];

        return self::tryFrom($action); // type safe, can be null if it doesn't match
    }
}
