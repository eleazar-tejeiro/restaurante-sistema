<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\User;
use Filament\Notifications\Notification;
class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;
    protected function afterCreate(): void
    {
        $this->notifyUsers('creado');
    }
    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
        Notification::make()
            ->title("Provedor {$actionType}")
            ->body("El provedor {$this->record->name} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}