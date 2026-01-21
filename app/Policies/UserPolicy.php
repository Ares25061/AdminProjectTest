<?php

namespace App\Policies;

use App\Models\User;
use App\Roles;

class UserPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function ban(User $user, User $model)
    {
        if ((($user->role === Roles::ADMIN || $user->role === Roles::MODER)&& $user->id !== $model->id) && ($model->role !== Roles::ADMIN || $model->role !== Roles::MODER)) {
            return true;
        }
        return false;
    }
    public function unban(User $user, User $model)
    {
        if ((($user->role === Roles::ADMIN || $user->role === Roles::MODER)&& $user->id !== $model->id) && ($model->role !== Roles::ADMIN || $model->role !== Roles::MODER)) {
            return true;
        }
        return false;
    }
    public function setRole(User $user, User $model)
    {
        if ($user->role === Roles::ADMIN && $user->id !== $model->id) {
            return true;
        }
        return false;
    }
}
