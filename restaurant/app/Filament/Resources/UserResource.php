<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Builder;
use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'Usuarios';
    protected static ?string $pluralModelLabel = 'Usuarios';
    protected static ?string $modelLabel = 'Usuario';
    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->label('Nombre'),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->unique(ignoreRecord: true)
                            ->label('Correo electrónico'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->label('Contraseña')
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->dehydrated(fn ($state) => filled($state))
                            ->visible(fn (string $context): bool => $context === 'create'),
                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->revealable()
                            ->required(fn (string $context): bool => $context === 'create')
                            ->maxLength(255)
                            ->label('Confirmar contraseña')
                            ->visible(fn (string $context): bool => $context === 'create')
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->same('password'),
                        Forms\Components\Select::make('roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->label('Roles'),
                    ])
                    ->columns(2),
            ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Correo electrónico'),
                Tables\Columns\TagsColumn::make('roles.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn(string $state):string =>match($state){
                        'Administrador' => 'primary',
                        'Vendedor' => 'success',
                    })
                    ->label('Rol'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha de creación'),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verificado')
                    ->trueIcon('heroicon-o-check-badge')
                    ->alignCenter()
                  
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado_verificacion')
                    ->options([
                        'verificados' => 'Verificados',
                        'no_verificados' => 'No verificados',
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['value'] === 'verificados', function (Builder $query) {
                                $query->whereNotNull('email_verified_at');
                            })
                            ->when($data['value'] === 'no_verificados', function (Builder $query) {
                                $query->whereNull('email_verified_at');
                            });
                    })
                    ->label('Estado de verificación'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('verificar')
                    ->icon('heroicon-o-check-badge')
                    ->action(function (User $user) {
                        $user->email_verified_at = now();
                        $user->save();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (User $user): bool => $user->email_verified_at !== null)
                    ->label('Verificar '),
                Tables\Actions\Action::make('desverificar')
                    ->icon('heroicon-o-x-mark')
                    ->action(function (User $user) {
                        $user->email_verified_at = null;
                        $user->save();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (User $user): bool => $user->email_verified_at === null)
                    ->label('Desverificar'),
                
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}