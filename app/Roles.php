<?php

namespace App;

enum Roles
{
    case Administrator;
    case User;
    case Moderator;
    public function role(): string
    {
        return match($this)
        {
            Roles::Administrator => 'administrator',
            Roles::Moderator => 'moderator',
            Roles::User => 'user',
        };
    }
}
