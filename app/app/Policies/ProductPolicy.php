<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
     public function viewAny(User $user): bool
    {
        return $user->can('ver productos');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('ver productos');
    }

    public function create(User $user): bool
    {
        return $user->can('crear productos');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('actualizar productos');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('eliminar productos');
    }
}
