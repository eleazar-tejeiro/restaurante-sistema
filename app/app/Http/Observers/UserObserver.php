<?php

namespace App\Observers;

use App\Models\User;
use Filament\Notifications\Notification;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        Notification::make()
            ->title('¡Bienvenido!')
            ->body("Hola {$user->name}, bienvenido a nuestra plataforma.")
            ->success()
            ->sendToDatabase($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        Notification::make()
            ->title('Perfil Actualizado')
            ->body('Tu perfil ha sido actualizado correctamente.')
            ->success()
            ->sendToDatabase($user);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // No enviamos notificación aquí ya que el usuario ha sido eliminado
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        Notification::make()
            ->title('Cuenta Restaurada')
            ->body('Tu cuenta ha sido restaurada exitosamente.')
            ->success()
            ->sendToDatabase($user);
    }

}