<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Purchase extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';   
    protected static ?string $title='Reportes de las compras';
    protected static ?string $navigationGroup='Reportes';
    protected static ?string $navigationLabel = 'Compras';
    protected static string $view = 'filament.pages.purchase';
    public function getBreadcrumbs(): array
    {
        return [
            'Compras',
            'Listado'
        ];
    }
}