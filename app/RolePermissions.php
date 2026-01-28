<?php

namespace App;

enum RolePermissions : string
{
    case CREATE = 'roles.create';
    case UPDATE = 'roles.update';
    case DELETE = 'roles.delete';
}
