<?php

use App\Models\User;

if (!function_exists('getUsersByRoles')) {
    /**
     * Get users with specific roles.
     *
     * @param array $roles
     * @return \Illuminate\Database\Eloquent\Collection
     */
    function getUsersByRoles(array $roles = ['Administrador', 'Vendedor'])
    {
        return User::whereHas('roles', function ($query) use ($roles) {
            $query->whereIn('name', $roles);
        })->get();
    }
}