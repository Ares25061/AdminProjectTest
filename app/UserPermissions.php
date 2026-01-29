<?php

namespace App;

enum UserPermissions : string
{
    case BAN = 'users.ban';
    case UNBAN = 'users.unban';
    case VIEW = 'users.view';
    case VIEW_LIST = 'users.view-list';
    case UPDATE = 'users.update';
    case DELETE = 'users.delete';
    public static function values(): array
    {
        $values = [];
        foreach (self::cases() as $permission) {
            $values[] = $permission->value;
        }
        return $values;
    }

    public static function moderPermissions(): array
    {
        return [
            self::BAN,
            self::UNBAN,
            self::VIEW,
            self::VIEW_LIST,
            self::UPDATE
        ];
    }
}
