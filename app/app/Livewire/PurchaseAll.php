<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\PurchaseDetail;
use App\Models\User;
use App\Models\Product;
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
class PurchaseAll extends Component implements HasForms, HasTable
{
    use InteractsWithTable;
    use InteractsWithForms;

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
            ->query(PurchaseDetail::query()->joinRelationship('purchase'))
            ->columns([
                TextColumn::make('purchase.id')
                    ->label('Compra ID')
                    ->sortable(),
                TextColumn::make('purchase.user.name')
                    ->label('Comprador')
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
                            ->label('Comprados')
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
                TextColumn::make('purchase.created_at')
                    ->label('Fecha y hora de compra')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('Creado desde')
                            ->reactive()
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Creado hasta')
                            ->reactive()
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_details.created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('purchase_details.created_at', '<=', $date),
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
                            fn (Builder $query, $userId): Builder => $query->whereHas('purchase', fn ($q) => $q->where('user_id', $userId))
                        );
                    })
            ])
            ->headerActions([
                Action::make('export')
                    ->label('Exportar en PDF')
                    ->icon('heroicon-o-document')
                    ->visible(fn () => auth()->user()->can('exportar reporte de compra a pdf'))
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
                            ->label('Filtrar por Usuario')
                            ->native(false)
                            ->options(User::pluck('name', 'id'))
                            ->placeholder('Todos los usuarios')
                            ->nullable()
                    ])
                    ->action(function (array $data) {
                        Session::put('pdf_start_date', $data['startDate']);
                        Session::put('pdf_end_date', $data['endDate']);
                        Session::put('pdf_user_id', $data['user_id']);
                        return redirect()->route('purchase.pdf');
                    })
            ]);
    }

    public function render()
    {
        return view('livewire.purchase-all');
    }
}