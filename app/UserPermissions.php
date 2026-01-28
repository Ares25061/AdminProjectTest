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

}
