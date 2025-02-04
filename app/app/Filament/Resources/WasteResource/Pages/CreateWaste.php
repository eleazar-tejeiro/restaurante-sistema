<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use App\Models\Inventory;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\InventoryMovement;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
class CreateWaste extends CreateRecord
{
    protected static string $resource = WasteResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $waste = $this->record;
            $inventory = Inventory::findOrFail($waste->inventory_id);
            $inventory->quantity -= $waste->quantity;
            $inventory->save();
            InventoryMovement::create([
                'inventory_id' => $waste->inventory_id,
                'movement_type' => 'output',
                'quantity' => $waste->quantity,
                'movement_date' => $waste->waste_date,
                'reference_id' => $waste->id,
                'reference_type' => 'waste',
            ]);
            $this->notifyUsers('creado');
        });
   
        
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