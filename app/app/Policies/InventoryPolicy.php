<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventoryPolicy
{

    public function viewAny(User $user): bool
    {
        return $user->can('ver reporte de inventario');
    }

    public function view(User $user, Product $product): bool
    {
        return $user->can('ver reporte de inventario');
    }
    public function exportInventoryPdf(User $user): bool
    {
        return $user->hasPermissionTo('exportar reporte inventario a pdf');
    }

    

    
}
