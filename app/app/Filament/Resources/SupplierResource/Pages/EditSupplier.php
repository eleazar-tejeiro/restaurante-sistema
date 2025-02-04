<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function afterSave(): void
    {
        $this->notifyUsers('editado');
    }

    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
        Notification::make()
            ->title("Proveedor {$actionType}")
            ->body("El proveedor {$this->record->name} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}