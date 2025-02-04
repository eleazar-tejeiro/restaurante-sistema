<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        $userPermissions = [
            'ver usuarios',
            'crear usuarios',
            'actualizar usuarios',
            'eliminar usuarios',
        ];
        $productPermissions = [
            'ver productos',
            'crear productos',
            'actualizar productos',
            'eliminar productos',
        ];
        $salePermissions = [
            'ver ventas',
            'crear ventas',
            'actualizar ventas',
            'eliminar ventas',
            'ver detalles ventas',
        ];

        $purchasePermissions = [
            'ver compras',
            'crear compras',
            'actualizar compras',
            'eliminar compras',
        ];

        $inventoryPermissions = [
            'ver inventario',
        ];
        $suppliesPermissions = [
            'ver proveedores',
            'crear proveedores',
            'actualizar proveedores',
            'eliminar proveedores',
        ];
        $wastePermissions = [
            'ver desperdicio',
            'crear desperdicio',
            'actualizar desperdicio',
            'eliminar desperdicio',
        ];
        $inventoryReportPermissions = [
            'exportar reporte de inventario a pdf',
        ];
        $saleReportPermissions = [
            'exportar reporte de venta a pdf',
        ];
        $purchaseReportPermissions = [
            'exportar reporte de compra a pdf',
        ];
        $rolePermissions = [
            'crear roles',
            'actualizar roles',
            'eliminar roles',
        ];
        
        $permissionPermissions = [
            'ver permisos',
            'actualizar permisos',
        ];
        $allPermissions = array_merge(
            $productPermissions,
            $salePermissions,
            $purchasePermissions,
            $suppliesPermissions,
            $wastePermissions,
            $userPermissions,
            $rolePermissions,          
            $permissionPermissions,   
            $inventoryReportPermissions,
            $saleReportPermissions,
            $purchaseReportPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}