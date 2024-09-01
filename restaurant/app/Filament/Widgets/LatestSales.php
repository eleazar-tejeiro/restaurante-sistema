<?php

namespace App\Filament\Widgets;

use App\Models\SaleDetail;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
class LatestSales extends BaseWidget
{
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';
    protected static ?string $heading = 'Ventas del Día';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SaleDetail::query()
                    ->joinRelationship('sale')
                    ->whereDate('sales.created_at', Carbon::today())
            )
            ->columns([
                TextColumn::make('sale.id')
                ->label('Venta ID')
                ->sortable(),
            TextColumn::make('sale.user.name')
                ->label('Vendedor')
                ->sortable()
                ->searchable(),
            TextColumn::make('product.name')
                ->label('Producto')
                ->searchable(),
            TextColumn::make('quantity')
                ->label('Cantidad')
                ->sortable()
                ->summarize([
                    Sum::make()
                        ->label('Vendidos')
                ]),
                TextColumn::make('subtotal')
                ->label('Subtotal')
                ->prefix('Bs ')
                ->sortable()
                ->summarize([
                    Sum::make()
                        ->label('Total')
                        ->formatStateUsing(fn (string $state): string => "Bs " . number_format((float)$state, 2, ',', '.'))
                ]),
            TextColumn::make('sale.created_at')
                ->label('Fecha y hora de venta')
                ->dateTime()
                ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50, 100])
            ->emptyStateHeading('No hay ventas hoy')
            ->emptyStateDescription('Las ventas del día aparecerán aquí cuando se realicen.');
    }

    public static function canView(): bool
    {
        return true;
    }
}