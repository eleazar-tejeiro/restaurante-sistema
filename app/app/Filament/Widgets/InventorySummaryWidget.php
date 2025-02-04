<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InventorySummaryWidget extends BaseWidget
{
    
    protected function getStats(): array
    {
        $totalProducts = Product::count();
        $lowStockProducts = Inventory::where('quantity', '<', 10)->count();
        $totalStock = Inventory::sum('quantity');

        return [
            Stat::make('Total de Productos', $totalProducts)
                ->description('Número total de productos en el catálogo')
                ->icon('heroicon-o-rectangle-stack'),
            
            Stat::make('Productos con Stock Bajo', $lowStockProducts)
                ->description('Productos con menos de 10 unidades')
                ->icon('heroicon-o-exclamation-circle')
                ->color('danger'),
            
            Stat::make('Stock Total', $totalStock)
                ->description('Suma de todas las unidades en inventario')
                ->icon('heroicon-o-cube'),
        ];
    }
}