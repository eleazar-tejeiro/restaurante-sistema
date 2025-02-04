<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Filament\Resources\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PurchaseDetail;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\User;

class EditPurchase extends EditRecord
{
    protected static string $resource = PurchaseResource::class;
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
                ->modalHeading('¿Estás seguro de que quieres restablecer esta compra?')
                ->modalDescription('Los cambios no guardados se perderán.')
                ->modalSubmitActionLabel('Sí, restablecer')
                ->modalCancelActionLabel('Cancelar'),
            $this->getCancelFormAction(),
        ];
    }

    protected function beforeSave(): void
    {
        DB::transaction(function () {
            $purchaseDetails = $this->record->details;
            foreach ($purchaseDetails as $detail) {
                $initialQuantity = $this->initialQuantities[$detail->id]['quantity'] ?? 0;
                $newQuantity = $detail->quantity;
                $inventory = Inventory::where('product_id', $detail->product_id)->firstOrFail();
                if ($inventory) {
                    $quantityDifference = $initialQuantity - $newQuantity;
                    $inventory->quantity += $quantityDifference;
                    $inventory->save();
                }
            }

            $this->notifyUsers('editada');
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function hasInventoryChanges(): bool
    {
        $purchaseDetails = PurchaseDetail::where('purchase_id', $this->record->id)->get();
        
        foreach ($purchaseDetails as $detail) {
            $inventory = Inventory::where('product_id', $detail->product_id)->first();
            
            if (!$inventory || $inventory->quantity < $detail->quantity) {
                return true;
            }
        }
        return false;
    }

    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);

        Notification::make()
            ->title("Compra {$actionType}")
            ->body("La compra #{$this->record->id} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
}