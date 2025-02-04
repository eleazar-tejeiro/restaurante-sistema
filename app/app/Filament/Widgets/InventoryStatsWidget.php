<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\Sale;
use App\Models\Purchase;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class InventoryStatsWidget extends BaseWidget
{
    protected static ?int $sort = -2;
    protected static ?string $column = 'col-span-full';
    protected function getStats(): array
    {
        return [
            Stat::make('Ventas del Mes', $this->getMonthlySales())
                ->description('Total de ventas del mes actual')
                ->icon('heroicon-o-currency-dollar')
                ->color('success'),
            
            Stat::make('Productos Más Vendidos', $this->getTopSellingProduct())
                ->description('Producto con más unidades vendidas este mes')
                ->icon('heroicon-o-fire')
                ->color('danger'),
            
            Stat::make('Valor del Inventario', $this->getInventoryValue())
                ->description('Valor total del inventario actual')
                ->icon('heroicon-o-cube')
                ->color('primary'),
            
            Stat::make('Productos Sin Stock', $this->getOutOfStockCount())
                ->description('Productos que necesitan reabastecimiento')
                ->icon('heroicon-o-exclamation-circle')
                ->color('warning'),
            
            Stat::make('Nuevos Productos', $this->getNewProductsCount())
                ->description('Productos añadidos este mes')
                ->icon('heroicon-o-plus-circle')
                ->color('success'),
            
            Stat::make('Compras del Mes', $this->getMonthlyPurchases())
                ->description('Total de compras a proveedores este mes')
                ->icon('heroicon-o-truck')
                ->color('info'),
        ];
    }

    private function getMonthlySales(): string
    {
        $totalSales = Sale::whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year)
                          ->sum('total');
        
        return number_format($totalSales, 2) . ' Bs';
    }

    private function getTopSellingProduct(): string
    {
        $topProduct = Product::join('sale_details', 'products.id', '=', 'sale_details.product_id')
                             ->join('sales', 'sales.id', '=', 'sale_details.sale_id')
                             ->whereMonth('sales.created_at', now()->month)
                             ->whereYear('sales.created_at', now()->year)
                             ->select('products.name', \DB::raw('SUM(sale_details.quantity) as total_quantity'))
                             ->groupBy('products.id', 'products.name')
                             ->orderByDesc('total_quantity')
                             ->first();
    
        return $topProduct ? $topProduct->name : 'N/A';
    }

    private function getInventoryValue(): string
    {
        $totalValue = Inventory::join('products', 'inventories.product_id', '=', 'products.id')
                               ->sum(\DB::raw('inventories.quantity * products.purchase_price'));

        return number_format($totalValue, 2) . ' Bs';
    }

    private function getOutOfStockCount(): int
    {
        return Inventory::where('quantity', 0)->count();
    }

    private function getNewProductsCount(): int
    {
        return Product::whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year)
                      ->count();
    }

    private function getMonthlyPurchases(): string
    {
        $totalPurchases = Purchase::whereMonth('created_at', now()->month)
                                  ->whereYear('created_at', now()->year)
                                  ->sum('total');

        return number_format($totalPurchases, 2) . ' Bs';
    }
}