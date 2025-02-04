<?php

namespace App\Filament\Resources\PurchaseResource\Pages;

use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Filament\Resources\PurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use App\Models\User;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function afterCreate(): void
    {
        DB::transaction(function () {
            $purchaseDetails = $this->record->details;
            foreach ($purchaseDetails as $detail) {
                $product = Product::findOrFail($detail->product_id);
                
                $inventory = Inventory::firstOrCreate(
                    ['product_id' => $detail->product_id],
                    [
                        'quantity' => 0,
                        'batch' => $detail->batch ?? null,
                        'expiration_date' => $detail->expiration_date ?? null,
                    ]
                );
        
                $inventory->quantity += $detail->quantity;
                
                $inventory->save();
        
                $product->purchase_price = $detail->unit_price;
                $product->save();
    
                InventoryMovement::create([
                    'inventory_id' => $inventory->id,
                    'movement_type' => 'input',
                    'quantity' => $detail->quantity,
                    'movement_date' => now(),
                    'reference_id' => $this->record->id,
                    'reference_type' => 'purchase',
                ]);
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
            ->title("Compra {$actionType}")
            ->body("La compra #{$this->record->id} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
}