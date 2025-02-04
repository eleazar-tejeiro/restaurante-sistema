<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $this->notifyUsers('creado');    
    }

    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);

        Notification::make()
            ->title("Usuario {$actionType}")
            ->body("El usuario {$this->record->name} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
