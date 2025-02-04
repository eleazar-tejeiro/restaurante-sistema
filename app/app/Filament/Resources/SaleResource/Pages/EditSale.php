<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Models\SaleDetail;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\User;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;    
    public $initialQuantities = [];

    public function mount($record): void
    {   
        parent::mount($record);
        foreach ($this->record->details as $detail) {
            $this->initialQuantities[$detail->id] = [
                'quantity' => $detail->quantity,
                'product_id' => $detail->product_id
            ];
        }
    }

    protected function beforeSave(): void
    {
        DB::transaction(function () {
            $saleDetails = $this->record->details;
            foreach ($saleDetails as $detail) {
                $initialQuantity = $this->initialQuantities[$detail->id]['quantity'] ?? 0;
                $newQuantity = $detail->quantity;
                $inventory = Inventory::where('product_id', $detail->product_id)->firstOrFail();
                if ($inventory) {
                    $quantityDifference = $initialQuantity - $newQuantity;
                    $inventory->quantity += $quantityDifference;
                    $inventory->save();

                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'movement_type' => $quantityDifference > 0 ? 'input' : 'output',
                        'quantity' => abs($quantityDifference),
                        'movement_date' => now(),
                        'reference_id' => $this->record->id,
                        'reference_type' => 'sale_edit',
                    ]);
                }
            }  
        });
    }

    protected function afterSave(): void
    {

        $this->notifyUsers('editada');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            Action::make('reset')
                ->label('Restablecer')
                ->color('danger')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->form->fill($this->record->fresh()->toArray());
                })
                ->requiresConfirmation()
                ->modalHeading('¿Estás seguro de que quieres restablecer esta venta?')
                ->modalDescription('Los cambios no guardados se perderán.')
                ->modalSubmitActionLabel('Sí, restablecer')
                ->modalCancelActionLabel('Cancelar'),
            $this->getCancelFormAction(),
        ];
    }

    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
        Notification::make()
            ->title("Venta {$actionType}")
            ->body("La venta #{$this->record->id} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
}