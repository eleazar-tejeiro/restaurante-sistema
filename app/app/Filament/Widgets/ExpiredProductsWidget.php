<?php

namespace App\Filament\Widgets;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PurchaseDetail;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ExpiredProductsWidget extends BaseWidget
{
    protected int|string|array $columnSpan = 'full';
    protected static ?int $sort = 5;
    protected static ?string $heading = 'Productos expirados';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseDetail::query()
                    ->whereDate('expiration_date', '<', now())
                    ->where('is_excluded', false)
                    ->whereHas('product')
                    ->with('product')
            )
            ->columns([
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('expiration_date')
                    ->label('Fecha de Vencimiento')
                    ->date(),
                TextColumn::make('quantity')
                    ->label('Cantidad Comprada'),
            ])
            ->actions([
                Action::make('remove_expired')
                    ->label('Retirar Vencidos')
                    ->form([
                        TextInput::make('quantity_to_remove')
                            ->label('Cantidad a retirar')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->rules(['required', 'numeric', 'min:1'])
                            ->validationMessages([
                                'required' => 'La cantidad a retirar es obligatoria',
                                'numeric' => 'La cantidad debe ser un número',
                                'min' => 'La cantidad debe ser mayor o igual a 1'
                            ])
                            ->validationAttribute('cantidad a retirar')
                    ])
                    ->action(function (PurchaseDetail $record, array $data): void {
                        $inventory = Inventory::where('product_id', $record->product_id)->firstOrFail();
                        if ($inventory->quantity < intval($data['quantity_to_remove'])) {
                            Notification::make()
                                ->danger()
                                ->title('Error')
                                ->body("La cantidad a retirar no puede ser mayor o menor al stock actual ({$inventory->quantity }).")
                                ->send();
                            return;
                        }else{
                            DB::transaction(function () use ($data, $record) {
                                $inventory = Inventory::where('product_id', $record->product_id)->firstOrFail();
                                $quantityToRemove = min($data['quantity_to_remove'], $inventory->quantity);
                                $inventory->quantity -= $quantityToRemove;
                                $inventory->save();

                                InventoryMovement::create([
                                    'inventory_id' => $inventory->id,
                                    'movement_type' => 'output',
                                    'quantity' => $quantityToRemove,
                                    'movement_date' => now(),
                                    'reference_id' => $record->id,
                                    'reference_type' => 'expired_product'
                                ]);

                                $record->is_excluded = true;
                                $record->save();
                                Notification::make()
                                    ->title('Guardado')
                                    ->success()
                                    ->send();
                            });
                        }
                    })
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Retirar productos vencidos')
                    ->modalDescription('¿Está seguro de que desea retirar estos productos vencidos del inventario?')
                    ->modalSubmitActionLabel('Sí, retirar'),

                Action::make('ignore')
                    ->label('Ignorar')
                    ->action(function (PurchaseDetail $record): void {
                        $record->is_excluded = true;
                        $record->save();
                        
                        Notification::make()
                            ->title('Guardado')
                            ->success()
                            ->send();
                    })
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Ignorar producto vencido')
                    ->modalDescription('¿Está seguro de que desea ignorar este producto vencido? Esta acción no afectará el inventario.')
                    ->modalSubmitActionLabel('Sí, ignorar')
            ])
            ->defaultSort('expiration_date', 'asc')
            ->poll('60s');
    }
}