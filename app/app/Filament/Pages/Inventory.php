<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Pages\Actions\Action;

class Inventory extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';

    protected static ?string $title = 'Inventario';
    
    protected static ?string $navigationGroup = 'Reportes';
    
    protected static ?string $navigationLabel = 'Inventario';
    
    protected static string $view = 'filament.pages.inventory';

    protected static ?string $recordTitleAttribute = 'name';
    
    public function getBreadcrumbs(): array
    {
        return [
            'Inventory',
            'Listado'
        ];
    }

}