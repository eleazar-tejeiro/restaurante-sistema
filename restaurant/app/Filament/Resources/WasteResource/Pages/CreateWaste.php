<?php

namespace App\Filament\Resources\WasteResource\Pages;

use App\Filament\Resources\WasteResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateWaste extends CreateRecord
{
    protected static string $resource = WasteResource::class;
    protected function afterCreate(): void
    {
        $waste = $this->record;
        $product = $waste->product;
        $product->stock -= $waste->quantity;
        $product->save();
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}