<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\User;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
class SaleAll extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;
    public $saleDetails;
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
                    ->sortable()
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
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('Fecha de venta desde')
                            ->reactive()
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Fecha de venta hasta')
                            ->reactive()
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_details.created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('sale_details.created_at', '<=', $date),
                            );
                    }),
                
                Filter::make('user')
                    ->form([
                        Select::make('user_id')
                            ->label('Vendedor')
                            ->options(User::pluck('name', 'id'))
                            ->reactive()
                            ->native(false)
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['user_id'],
                            fn (Builder $query, $userId): Builder => $query->whereHas('sale', fn ($q) => $q->where('user_id', $userId))
                        );
                    })
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar en PDF')
                    ->icon('heroicon-o-document')
                    ->visible(fn () => auth()->user()->can('exportar reporte de venta a pdf'))
                    ->form([
                        DatePicker::make('startDate')
                            ->label('Fecha de inicio')
                            ->native(false)
                            ->default($this->startDate),
                        DatePicker::make('endDate')
                            ->label('Fecha de fin')
                            ->native(false)
                            ->default($this->endDate),
                        Select::make('user_id')
                            ->label('Filtrar por Vendedor')
                            ->native(false)
                            ->options(User::pluck('name', 'id'))
                            ->placeholder('Todos los vendedores')
                            ->nullable()
                    ])
                    ->action(function (array $data) {
                        Session::put('pdf_start_date', $data['startDate']);
                        Session::put('pdf_end_date', $data['endDate']);
                        Session::put('pdf_user_id', $data['user_id']);
                        return redirect()->route('sale.pdf');
                    })
            ]);
    }
    public function render()
    {
        return view('livewire.sale-all');
    }
}