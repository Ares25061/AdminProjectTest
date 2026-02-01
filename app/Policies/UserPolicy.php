<?php

namespace App\Policies;

use App\Models\Role;
use App\Models\User;
use App\RolePermissions;
use App\Roles;
use App\UserPermissions;
use Illuminate\Auth\Access\Response;

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
        if ($user->hasPermission(UserPermissions::VIEW) || $user->id === $model->id) {
            return Response::allow();
        }
        return Response::deny("You don't have permission to view users");

    }
    public function viewList(User $user)
    {
        if ($user->hasPermission(UserPermissions::VIEW_LIST)) {
            return Response::allow();
        }
        return Response::deny("You don't have permission to view list users");
    }
    public function update(User $user, User $model)
    {
        if (!$user->hasPermission(UserPermissions::UPDATE)) {
            return Response::deny("You don't have permission to update users");
        }
        if ($user->role === Roles::MODER && $model->role !== Roles::USER) {
            return Response::deny("You cant update another moderators and administrators");
        }
        return Response::allow();
    }
    public function delete(User $user, User $model)
    {
        if (!$user->hasPermission(UserPermissions::DELETE)) {
            return Response::deny("You don't have permission to delete users");
        }
        if ($user->id === $model->id) {
            return Response::deny("You cant delete yourself");
        }
        if ($user->role === Roles::MODER && ($model->role === Roles::ADMIN || $model->role === Roles::MODER)) {
            return Response::deny("You cant delete another moderators and administrators");
        }
        return Response::allow();
    }
    public function ban(User $user, User $model)
    {
        if (!$user->hasPermission(UserPermissions::BAN))
        {
            return Response::deny("You don't have permission to ban users");
        }
        if ($model->role === Roles::MODER || $model->role === Roles::ADMIN)
        {
            return Response::deny("You cant ban moderators and administrators");
        }
        if ($user->id === $model->id)
        {
            return Response::deny("You cant ban yourself");
        }
        return Response::allow();
    }
    public function unban(User $user, User $model)
    {
        if (!$user->hasPermission(UserPermissions::UNBAN))
        {
            return Response::deny("You don't have permission to unban users");
        }
        if ($user->id === $model->id)
        {
            return Response::deny("You cant unban yourself");
        }
        return Response::allow();
    }
    public function setRole(User $user, User $model)
    {
        if ($user->hasPermission(RolePermissions::SET))
        {
            return Response::deny("You don't have permission to set role users");
        }
        if ($user->id === $model->id)
        {
            return Response::deny("You cant change role yourself");
        }
        return Response::allow();
    }
}
