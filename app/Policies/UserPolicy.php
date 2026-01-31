<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\RolePermissions;
use App\Roles;
use App\UserPermissions;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function view(User $user, User $model)
    {
        if ($user->hasPermission(UserPermissions::VIEW)) {
            return true;
        }
        if ($user->id === $model->id) {
            return true;
        }
        return false;
    }
    public function viewList(User $user)
    {
        if ($user->hasPermission(UserPermissions::VIEW_LIST)) {
            return true;
        }
        return false;
    }
    public function update(User $user, User $model)
    {
        if ($user->hasPermission(UserPermissions::UPDATE)) {
            return true;
        }
        if ($user->role === Roles::MODER && $model->role === Roles::USER) {
            return true;
        }
        return false;
    }
    public function delete(User $user, User $model)
    {
        if ($user->hasPermission(UserPermissions::DELETE)) {
            return true;
        }
        if ($user->id !== $model->id) {
            return true;
        }
        if ($user->role === Roles::MODER && $model->role === Roles::USER) {
            return true;
        }
        return false;
    }
    public function ban(User $user, User $model)
    {
        if ($user->hasPermission(UserPermissions::BAN))
        {
            return true;
        }
        if ($model->role === Roles::USER && $user->id !== $model->id)
        {
            return true;
        }
        return false;
    }
    public function unban(User $user, User $model)
    {
        if ($user->hasPermission(UserPermissions::UNBAN))
        {
            return true;
        }
        if ($model->role === Roles::USER && $user->id !== $model->id)
        {
            return true;
        }
        return false;
    }
    public function setRole(User $user, User $model)
    {
        if ($user->hasPermission(RolePermissions::SET))
        {
            return true;
        }
        if ($user->id !== $model->id)
        {
            return true;
        }
        return false;
    }
}
