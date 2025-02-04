<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Waste;
use Illuminate\Auth\Access\Response;

class WastePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver desperdicio');
    }

    public function view(User $user, Waste $waste): bool
    {
        return $user->can('ver desperdicio');
    }

    public function create(User $user): bool
    {
        return $user->can('crear desperdicio');
    }

    public function update(User $user, Waste $waste): bool
    {
        return $user->can('actualizar desperdicio');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Waste $waste): bool
    {
        return $user->can('eliminar desperdicio');
    }

}
