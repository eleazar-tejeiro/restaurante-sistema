<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Models\Supplier;
use Filament\Forms;
use App\Models\Purchase;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Card;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $modelLabel = 'Proveedor';

    protected static ?string $pluralModelLabel = 'Proveedores';

    protected static ?string $navigationLabel = 'Proveedores';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

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
                            ->unique(ignorable: fn ($record) => $record)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                                'unique' => 'El producto ya ha sido registrado.',
                            ]),
                        TextInput::make('address')
                            ->label('Dirección')
                            ->maxLength(255)
                            ->validationMessages([
                                'max' => 'La dirección no debe exceder los 255 caracteres.',
                            ]),
                        TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->prefix('+591')
                            ->regex('/^[1-9]\d{7}$/')
                            ->maxLength(8)
                            ->validationMessages([
                                'regex' => 'El formato del número de teléfono boliviano no es válido.',
                                'max' => 'El número de teléfono debe tener exactamente 8 dígitos.',
                            ])
                            ->afterStateHydrated(function (TextInput $component, $state) {
                                $component->state(ltrim($state, '+591'));
                            })
                            ->dehydrateStateUsing(function ($state) {
                                return '+591' . $state;
                            })
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('address')
                    ->label('Dirección')
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('Fecha de desperdicio desde')
                            ->reactive()
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Fecha de desperdicio hasta')
                            ->reactive()
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])

            ->actions([
                ViewAction::make()->label('Ver'),
                EditAction::make()->label('Editar'),
                Action::make('delete')
                    ->label('Eliminar')
                    ->requiresConfirmation()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function (Supplier $record , Action  $action) {
                        $purchaseCount = Purchase::where('supplier_id', $record->id)->count();
                        if ($purchaseCount > 0 ) {
                            Notification::make()
                                ->title('No se puede eliminar el provedor')
                                ->body('El provedor no se puede eliminar porque tiene otros recursos asociados.')
                                ->danger()
                                ->send();
                                $action->cancel(); 
                        }else{
                            $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
                            Notification::make()
                                ->title("Provedor eliminado")
                                ->body("El provedor {$record->name} ha sido eliminado por el usuario " . auth()->user()->name . ".")
                                ->success()
                                ->sendToDatabase($usersToNotify);  
                            $record->delete();    
                            $action->success();  
                            Notification::make()
                                ->title('Guardado')
                                ->success()
                                ->send();
                        }
                      
                    }),
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}