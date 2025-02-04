<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    
    protected static ?string $navigationLabel = 'Roles';

    protected static ?string $pluralModelLabel = 'Roles';
    
    protected static ?string $modelLabel = 'Rol';

    protected static ?string $navigationGroup = 'Roles y Permisos';

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            
                            ->unique(Role::class, 'name', ignoreRecord: true),
                            
                        Select::make('permissions')
                            ->label('Permisos')
                            ->multiple()
                            ->relationship('permissions', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('name')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('permissions_count')
                    ->label('Permisos')
                    ->counts('permissions')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                    
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            
            ->actions([
                ViewAction::make()->label('Ver'),
                EditAction::make()->label('Editar'),
                DeleteAction::make()->label('eliminar'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}