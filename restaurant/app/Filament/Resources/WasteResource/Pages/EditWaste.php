<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Product;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class EditWaste extends EditRecord
{
    protected static string $resource = WasteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $oldQuantity = $record->quantity;
        $newQuantity = $data['quantity'];
        $productId = $data['product_id'];

        $product = Product::findOrFail($productId);
        $quantityDifference = $newQuantity - $oldQuantity;

        if ($quantityDifference > 0) {
            if ($product->stock >= $quantityDifference) {
                $product->stock -= $quantityDifference;
            } else {
                Notification::make()
                    ->title('Stock insuficiente')
                    ->body("No hay suficiente stock para el producto '{$product->name}'.")
                    ->danger()
                    ->send();
                
                $data['quantity'] = $oldQuantity;
            }
        } else {
            $product->stock += abs($quantityDifference);
        }

        $product->save();
        $record->update($data);

        Notification::make()
            ->title('Desperdicio actualizado')
            ->success()
            ->send();
        return $record;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}