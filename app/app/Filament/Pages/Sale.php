<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Sale extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $title='Reportes de las ventas';
    protected static ?string $navigationGroup='Reportes';
    protected static ?string $navigationLabel = 'Ventas';
    protected static string $view = 'filament.pages.sale';
    public function getBreadcrumbs(): array
    {
        return [
            'Ventas',
            'Listado'
        ];
    }
}