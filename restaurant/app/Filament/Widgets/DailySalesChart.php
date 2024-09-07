<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailySalesChart extends ChartWidget
{
    protected static ?string $heading = 'Ventas Diarias (Última Semana)';
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $sales = Sale::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as daily_total'))
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        $daysInSpanish = [
            'Mon' => 'Lun',
            'Tue' => 'Mar',
            'Wed' => 'Mié',
            'Thu' => 'Jue',
            'Fri' => 'Vie',
            'Sat' => 'Sáb',
            'Sun' => 'Dom',
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dayName = Carbon::now()->subDays($i)->format('D');
            $labels[] = $daysInSpanish[$dayName];
            $dailySale = $sales->firstWhere('date', $date);
            $data[] = $dailySale ? round($dailySale->daily_total, 2) : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas Diarias',
                    'data' => $data,
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
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
                        'text' => 'Ventas (Bs)'
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Día de la Semana'
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.3
                ]
            ]
        ];
    }
}