<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver proveedores');
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can('ver proveedores');
    }

    public function create(User $user): bool
    {
        return $user->can('crear proveedores');
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can('actualizar proveedores');
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->can('eliminar proveedores');
    }
}
