<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TagsColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Spatie\Permission\Models\Role;
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    
    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';
    
    protected static ?string $navigationGroup = 'Gestión de Accesos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Usuario')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->label('Nombre'),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->unique(ignoreRecord: true)
                            ->label('Correo electrónico'),
                        TextInput::make('password')
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
                        TextInput::make('password_confirmation')
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
                        Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nombre'),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label('Correo electrónico'),
                    TagsColumn::make('roles.name')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->colors(fn () => static::getRoleColors())
                    ->label('Rol'),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Fecha de creación'),
                IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Verificado')
                    ->trueIcon('heroicon-o-check-badge')
                    ->alignCenter()
            ])
            ->filters([
                SelectFilter::make('estado_verificacion')
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
                EditAction::make(),
                Action::make('verificar')
                    ->icon('heroicon-o-check-badge')
                    ->action(function (User $user) {
                        $user->email_verified_at = now();
                        $user->save();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (User $user): bool => $user->email_verified_at !== null)
                    ->label('Verificar '),
                Action::make('desverificar')
                    ->icon('heroicon-o-x-mark')
                    ->action(function (User $user) {
                        $user->email_verified_at = null;
                        $user->save();
                    })
                    ->requiresConfirmation()
                    ->hidden(fn (User $user): bool => $user->email_verified_at === null)
                    ->label('Desverificar'),
            ]);
    }

    protected static function getRoleColors(): array
    {
        $roles = Role::all()->pluck('name')->toArray();
        $colors = ['primary', 'success', 'warning', 'danger', 'info'];
        
        return array_combine(
            $roles,
            array_map(fn ($index) => $colors[$index % count($colors)], array_keys($roles))
        );
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