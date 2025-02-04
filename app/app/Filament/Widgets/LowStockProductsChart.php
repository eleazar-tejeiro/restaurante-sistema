<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class LowStockProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Productos con Bajo Stock';
    protected static ?string $pollingInterval = '15m';
    protected function getData(): array
    {
        $lowStockProducts = Inventory::with('product')
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        $labels = $lowStockProducts->pluck('product.name')->toArray();
        $data = $lowStockProducts->pluck('quantity')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad en Stock',
                    'data' => $data,
                    'backgroundColor' => [
                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                        '#FF9F40', '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0'
                    ],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Cantidad en Stock',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Productos',
                    ],
                ],
            ],
            'indexAxis' => 'y',
        ];
    }
}