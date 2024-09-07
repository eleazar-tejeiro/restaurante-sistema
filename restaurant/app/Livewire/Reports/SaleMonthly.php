<?php

namespace App\Livewire\Reports;

use App\Models\SaleDetail;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Illuminate\Support\Carbon;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Facades\Auth;
class SaleMonthly extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

    public $selectedMonth;

    public function mount()
    {
        $this->selectedMonth = Carbon::now()->month;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(SaleDetail::query()->joinRelationship('sale'))
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
                    ->visible(fn () => (Auth::user()->hasRole('Administrador')))
                    ->label('Exportar en PDF')
                    ->icon('heroicon-o-document')
                    ->url(fn () => route('saleMonthly.pdf', ['month' => $this->selectedMonth]))
                    ->openUrlInNewTab(),
            ])
            ->filters([
                SelectFilter::make('month')
                    ->label('Mes')
                    ->options([
                        1 => 'Enero',
                        2 => 'Febrero',
                        3 => 'Marzo',
                        4 => 'Abril',
                        5 => 'Mayo',
                        6 => 'Junio',
                        7 => 'Julio',
                        8 => 'Agosto',
                        9 => 'Septiembre',
                        10 => 'Octubre',
                        11 => 'Noviembre',
                        12 => 'Diciembre',
                    ])
                    ->default(Carbon::now()->month)
                    ->query(function (Builder $query, array $data) {
                        $query->when(
                            $data['value'],
                            function (Builder $query, $month) {
                                $this->selectedMonth = $month;
                                return $query->whereMonth('sales.created_at', $month);
                            }
                        );
                    })
            ])
            ->defaultSort('sale.created_at', 'desc')
            ->searchable();
    }

    public function render(): View
    {
        return view('livewire.sale-monthly');
    }
}