<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\SaleDetail;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Productos Más Vendidos';
     protected static ?string $pollingInterval = '15m';
    protected function getData(): array
    {
        $topProducts = SaleDetail::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('product_id')
            ->orderByDesc('total_quantity')
            ->limit(5)
            ->with('product')
            ->get();

        $labels = $topProducts->pluck('product.name')->toArray();
        $data = $topProducts->pluck('total_quantity')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad Vendida',
                    'data' => $data,
                    'backgroundColor' => [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF',
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
                        'text' => 'Cantidad Vendida',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Productos',
                    ],
                ],
            ],
        ];
    }
}