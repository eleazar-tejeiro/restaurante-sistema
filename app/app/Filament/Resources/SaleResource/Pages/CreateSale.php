<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Filament\Resources\SaleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\User;

class CreateSale extends CreateRecord
{
    protected static string $resource = SaleResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $saleDetails = $this->record->details;
            foreach ($saleDetails as $detail) {
                $inventory = Inventory::where('product_id', $detail->product_id)->firstOrFail();
                if ($inventory) { 
                    $inventory->quantity -= $detail->quantity;
                    $inventory->save();
                    InventoryMovement::create([
                        'inventory_id' => $inventory->id,
                        'movement_type' => 'output',
                        'quantity' => $detail->quantity,
                        'movement_date' => now(),
                        'reference_id' => $this->record->id,
                        'reference_type' => 'sale',
                    ]);
                }
            }
        });
        $this->notifyUsers('creada');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
        Notification::make()
            ->title("Venta {$actionType}")
            ->body("La Venta #{$this->record->id} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
}