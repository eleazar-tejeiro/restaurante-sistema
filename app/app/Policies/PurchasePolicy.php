<?php

namespace App\Policies;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PurchasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver compras');
    }

    public function view(User $user, Purchase $purchase): bool
    {
        return $user->can('ver compras');
    }

    public function create(User $user): bool
    {
        return $user->can('crear compras');
    }

    public function update(User $user, Purchase $purchase): bool
    {
        return $user->can('actualizar compras');
    }

    public function delete(User $user, Purchase $purchase): bool
    {
        return $user->can('eliminar compras');
    }
}
