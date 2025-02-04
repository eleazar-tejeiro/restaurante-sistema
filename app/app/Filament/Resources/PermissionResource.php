<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Card;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationLabel = 'Permisos';

    protected static ?string $pluralModelLabel = 'Permisos';
    
    protected static ?string $modelLabel = 'Permiso';

    protected static ?string $navigationGroup = 'Roles y Permisos';
    
    protected static ?int $navigationSort = 3;

    public static function canCreate(): bool
    {
        return false;
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
   
    public static function form(Form $form): Form
    {
        $isEditMode = $form->getOperation() === 'edit';
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->disabled(fn () => $isEditMode)
                            ->maxLength(255)
                            ->unique(Permission::class, 'name', ignoreRecord: true),
                            
                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable(),
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
                    ->disabled(fn () => $isEditMode)
                    ->searchable(),    
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
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('roles');
    }
    

}