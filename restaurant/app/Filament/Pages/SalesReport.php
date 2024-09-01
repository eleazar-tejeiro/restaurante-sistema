<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class SalesReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static ?string $title='Inventario de ventas.';
    public static function getNavigationGroup(): ?string
    {
        return 'Inventarios';
    }
    protected static ?string $navigationLabel = 'Ventas';

   
    
    protected static string $view = 'filament.pages.sales-report';
    public $selectedTab = 'all';

    public function selectTab($tab)
    {
        $this->selectedTab = $tab;
    }
   
}