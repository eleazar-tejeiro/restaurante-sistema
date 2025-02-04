<?php

namespace App\Policies;

use Spatie\Permission\Models\Role;  
use App\Models\User;

class RolePolicy
{

    public function viewAny(User $user): bool
    {
        #return $user->hasPermissionTo('ver roles');
        return true;
    }

    public function view(User $user, Role $role): bool
    {
        
        #return $user->hasPermissionTo('ver roles');
        return true;
   
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('crear roles');
        #return true;
    
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('actualizar roles');
        #return true;
    
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('eliminar roles');
        #return true;
    }
}