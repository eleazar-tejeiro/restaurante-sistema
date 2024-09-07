<?php

namespace App\Livewire\Reports;

use App\Models\SaleDetail;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Facades\Auth;
class SaleToday extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

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
            ->headerActions([
                Action::make('export')
                    ->label('Exportar en pdf')
                    ->requiresConfirmation()
                    ->visible(fn () => (Auth::user()->hasRole('Administrador')))
                    ->icon('heroicon-o-document')
                    ->action(function () {
                        return redirect()->route('saleToday.pdf');
                    }),
            ])
            ->defaultSort('sale.created_at', 'desc');
    }

    public function render(): View
    {
        return view('livewire.sale-today');
    }
}