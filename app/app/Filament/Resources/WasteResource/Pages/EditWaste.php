<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\User;
class EditWaste extends EditRecord
{
    protected static string $resource = WasteResource::class;
    public $initialQuantity;

    public function mount($record): void
    {
        parent::mount($record);
        $this->initialQuantity = $this->record->quantity;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function () {
                    DB::transaction(function () {
                        $waste = $this->record;
                        $inventory = Inventory::findOrFail($waste->inventory_id);
                        $inventory->quantity += $waste->quantity;
                        $inventory->save();

                        InventoryMovement::create([
                            'inventory_id' => $waste->inventory_id,
                            'movement_type' => 'input',
                            'quantity' => $waste->quantity,
                            'movement_date' => now(),
                            'reference_id' => $waste->id,
                            'reference_type' => 'waste_deletion',
                        ]);
                        $this->notifyUsers('eliminado');
                    });
                }),
        ];
    }

    protected function afterSave(): void
    {
        DB::transaction(function () {
            $newQuantity = $this->record->quantity;
            $difference = $this->initialQuantity - $newQuantity;
            
            $inventory = Inventory::findOrFail($this->record->inventory_id);
            if ($inventory) {
                $inventory->quantity += $difference;
                $inventory->save();

                InventoryMovement::create([
                    'inventory_id' => $this->record->inventory_id,
                    'movement_type' => $difference > 0 ? 'input' : 'output',
                    'quantity' => abs($difference),
                    'movement_date' => now(),
                    'reference_id' => $this->record->id,
                    'reference_type' => 'waste_edit',
                ]);
                $this->notifyUsers('editado');
            }
        });
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
        $productName = $this->record->inventory->product->name;
        Notification::make()
            ->title("Desperdicio {$actionType}")
            ->body("Se ha {$actionType} un desperdicio del producto {$productName} del inventario  por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
}