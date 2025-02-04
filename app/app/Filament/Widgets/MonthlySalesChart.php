<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonthlySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Ventas Mensuales';
    protected static ?string $pollingInterval = '15m';
    protected function getData(): array
    {
        $sales = Sale::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('SUM(total) as total_sales')
        )
            ->where('created_at', '>=', Carbon::now()->subYear())
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $labels = $sales->pluck('month')->map(function ($month) {
            return Carbon::createFromFormat('Y-m', $month)->format('M Y');
        })->toArray();

        $data = $sales->pluck('total_sales')->toArray();

        return [
            'datasets' => [
                [
                    'label' => 'Ventas Totales',
                    'data' => $data,
                    'borderColor' => '#4BC0C0',
                    'fill' => false,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Ventas Totales',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Mes',
                    ],
                ],
            ],
        ];
    }
}