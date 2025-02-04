<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class SalesChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Ventas de los Últimos 7 Días';
        protected static ?string $pollingInterval = '15m';
    protected function getData(): array
    {
        $sales = Sale::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total) as total_sales')
        )
            ->whereBetween('created_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $dates = [];
        $totals = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $sale = $sales->firstWhere('date', $date);
            
            $dates[] = $date;
            $totals[] = $sale ? $sale->total_sales : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas Totales',
                    'data' => $totals,
                ],
            ],
            'labels' => $dates,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}