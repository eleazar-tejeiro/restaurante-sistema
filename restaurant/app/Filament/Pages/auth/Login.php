<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Forms\Components\TextInput;

class Login extends BaseLogin
{
    public function getCredentialsFormComponent(): TextInput
    {
        return TextInput::make('name')
            ->label('Nombre')
            ->required()
            ->autocomplete()
            ->autofocus();
    }
}