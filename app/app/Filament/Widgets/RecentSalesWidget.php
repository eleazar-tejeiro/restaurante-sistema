<?php

namespace App\Filament\Widgets;

use App\Models\Sale;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentSalesWidget extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = 'Últimas ventas';
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Sale::query()->latest()->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID Venta'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Venta')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('total')
                    ->prefix('Bs ')
                    ->label('Total'),
                   
            ]);
    }
}