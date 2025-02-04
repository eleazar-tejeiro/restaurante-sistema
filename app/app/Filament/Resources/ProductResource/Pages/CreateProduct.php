<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use App\Models\User;
use Spatie\Permission\Models\Role;
class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->notifyUsers('creado');    
    }

    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);

        Notification::make()
            ->title("Producto {$actionType}")
            ->body("El producto {$this->record->name} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}