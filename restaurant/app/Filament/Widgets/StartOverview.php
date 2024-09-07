<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Waste;

class StartOverview extends BaseWidget
{
    protected static ?int $sort =1;
    protected function getStats(): array
    {
    
        return [
            Stat::make('Ventas Totales', Sale::sum('total'))
                ->description('Total de ventas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Productos en Stock', Product::sum('stock'))
                ->description('Total de productos')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
            Stat::make('Desperdicios', Waste::sum('quantity'))
                ->description('Total de productos desperdiciados')
                ->descriptionIcon('heroicon-m-trash')
                ->color('danger'),
        ];
    }
}