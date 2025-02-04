<?php

namespace App\Livewire;
use App\Models\Product;
use Livewire\Component;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use App\Models\Inventory;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
class InventoryAll extends Component implements HasTable, HasForms
{
    use InteractsWithForms, InteractsWithTable;
    
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->startDate = now()->subMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Inventory::query()->with('product'))
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Stock Actual')
                    ->sortable(),
                TextColumn::make('product.purchase_price')
                    ->label('Precio de Compra')
                    ->prefix('Bs ')
                    ->sortable(),
                TextColumn::make('product.sale_price')
                    ->label('Precio de Venta')
                    ->prefix('Bs ')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de creación')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime()
                    ->sortable(),
                BadgeColumn::make('expiration_date')
                    ->label('Fecha de Caducidad')
                    ->date()
                    ->sortable()
                    ->color(fn ($record): string => 
                        $record->expiration_date && $record->expiration_date->isPast() 
                            ? 'danger' 
                            : ($record->expiration_date && $record->expiration_date->diffInDays(now()) <= 7 ? 'warning' : 'success')
                    ),
            ])
            ->filters([
                Filter::make('created_at')
                        ->form([
                            DatePicker::make('from')
                                ->label('Fecha de compra desde')
                                ->reactive()
                                ->native(false),
                            DatePicker::make('until')
                                ->label('Fecha de compra hasta')
                                ->reactive()
                                ->native(false),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $query
                                ->when(
                                    $data['from'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                                )
                                ->when(
                                    $data['until'],
                                    fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                                );
                        }),
                
    
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar en PDF')
                    ->visible(fn () => auth()->user()->can('exportar reporte de inventario a pdf'))
                    ->icon('heroicon-o-document')
                    ->form([
                        DatePicker::make('startDate')
                            ->label('Fecha de inicio')
                            ->native(false)
                            ->default($this->startDate),
                        DatePicker::make('endDate')
                            ->label('Fecha de fin')
                            ->native(false)
                            ->default($this->endDate),
                    ])
                    ->action(function (array $data) {
                        Session::put('pdf_start_date', $data['startDate']);
                        Session::put('pdf_end_date', $data['endDate']);
                        return redirect()->route('inventory.pdf');
                    })
                   
            ]);
    }

    public function render()
    {
        return view('livewire.inventory-all');
    }
}