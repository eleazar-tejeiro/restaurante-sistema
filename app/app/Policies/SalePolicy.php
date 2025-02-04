<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver ventas');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->can('ver ventas');
    }

    public function create(User $user): bool
    {
        return $user->can('crear ventas');
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->can('actualizar ventas');
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->can('eliminar ventas');
    }
}