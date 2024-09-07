<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\SaleDetail;
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
            ->headerActions([
                Action::make('export')
                    ->label('Exportar en PDF')
                    ->icon('heroicon-o-document')
                    ->form([
                        DatePicker::make('startDate')
                            ->label('Fecha de inicio')
                            ->default($this->startDate),
                        DatePicker::make('endDate')
                            ->label('Fecha de fin')
                            ->default($this->endDate),
                    ])
                    ->action(function (array $data) {
                        Session::put('pdf_start_date', $data['startDate']);
                        Session::put('pdf_end_date', $data['endDate']);
                        return redirect()->route('saleAll.pdf');
                    })
                    ->visible(fn () => (Auth::user()->hasRole('Administrador'))),
            ]);
    }
    public function render()
    {
        return view('livewire.sale-all');
    }
}