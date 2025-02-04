<?php

namespace App\Policies;

use Spatie\Permission\Models\Permission;  
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('ver permisos');
        #return true;
    }

    public function view(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('ver permisos');
        #return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crear permisos');
        #return true;
    }

    public function update(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('actualizar permisos');
        #return true;

    }

    public function delete(User $user, Permission $permission): bool
    {
        return $user->hasPermissionTo('eliminar permisos');
        #return true;
    }

}