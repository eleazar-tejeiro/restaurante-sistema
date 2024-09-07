<?php

namespace App\Filament\Resources\SaleResource\Pages;

use App\Models\Product;
use App\Models\SaleDetail;
use App\Filament\Resources\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSale extends EditRecord
{
    protected static string $resource = SaleResource::class;
    public $oldQuantity = [];
    public $originalSaleDetails = [];
    public $deletedItems = [];

    protected function beforeFill(): void
    {
        $this->originalSaleDetails = $this->record->saleDetails->keyBy('id')->toArray();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['saleDetails'] = $this->record->saleDetails->map(function ($detail) {
            return [
                'id' => $detail->id,
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal' => $detail->subtotal,
            ];
        })->toArray();

        return $data;
    }

    protected function beforeSave(): void
    {
        $sale = $this->record;

        // Identificar items eliminados
        $currentDetailIds = collect($this->data['saleDetails'])->pluck('id')->filter()->toArray();
        $deletedDetails = array_diff_key($this->originalSaleDetails, array_flip($currentDetailIds));

        foreach ($deletedDetails as $deletedDetail) {
            $this->deletedItems[] = [
                'product_id' => $deletedDetail['product_id'],
                'quantity' => $deletedDetail['quantity'],
            ];
        }

        // Manejar items eliminados
        foreach ($this->deletedItems as $deletedItem) {
            $product = Product::findOrFail($deletedItem['product_id']);
            $product->stock += $deletedItem['quantity'];
            $product->save();
        }

        // Manejar items actualizados y nuevos
        foreach ($this->data['saleDetails'] as $index => $detailData) {
            $product = Product::findOrFail($detailData['product_id']);
            $saleDetail = isset($detailData['id']) 
                ? SaleDetail::find($detailData['id'])
                : null;

            $newQuantity = $detailData['quantity'];
            $oldQuantity = $saleDetail ? $saleDetail->quantity : 0;
            $quantityDifference = $newQuantity - $oldQuantity;

            $availableStock = $product->stock + $oldQuantity;
            if ($newQuantity > $availableStock) {
                $this->addError("saleDetails.{$index}.quantity", "No hay suficiente stock para el producto '{$product->name}'. Stock disponible: {$availableStock}");
                return;
            }

            $product->stock = $availableStock - $newQuantity;
            $product->save();
        }

        // Limpiar los items eliminados después de procesarlos
        $this->deletedItems = [];
    }

    protected function afterSave(): void
    {
        $this->record->saleDetails()->saveMany(
            collect($this->data['saleDetails'])->map(function ($detail) {
                return new SaleDetail($detail);
            })
        );

        $this->record->total = collect($this->data['saleDetails'])->sum('subtotal');
        $this->record->save();
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Venta actualizada')
            ->body('La venta ha sido actualizada exitosamente y el stock ha sido ajustado.');
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}