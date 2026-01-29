<?php

namespace App;

enum RolePermissions : string
{
    case CREATE = 'roles.create';
    case UPDATE = 'roles.update';
    case DELETE = 'roles.delete';
    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $permission) {
            $values[] = $permission->value;
        }
        return $values;
    }
}
