<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteResource\Pages;
use App\Models\Waste;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\BadgeColumn;
class WasteResource extends Resource
{
    protected static ?string $model = Waste::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationLabel = 'Desperdicios';

    protected static ?string $pluralModelLabel = 'Desperdicios';

    protected static ?string $modelLabel = 'Desperdicio';
    
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Desperdicio')
                    ->schema([
                    Grid::make()
                        ->schema([
                        Select::make('inventory_id')
                            ->label('Producto')
                            ->options(Inventory::all()->pluck('product.name', 'id'))
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->options(function (Get $get) {
                                $selectedProducts = collect($get('../*.product_id'))
                                    ->filter(fn ($id) => $id !== $get('product_id'))
                                    ->toArray();

                                return Product::whereIn('id', function ($query) {
                                        $query->select('product_id')
                                            ->from('inventories')
                                            ->where('quantity', '>', 1);
                                    })
                                    ->whereNotIn('id', $selectedProducts)
                                    ->pluck('name', 'id');
                            })
                            ->exists('inventories', 'id')
                            ->rules(['required', 'integer'])
                            ->validationMessages([
                                'required' => 'El producto es obligatorio.',
                                'exists' => 'El producto seleccionado no existe en el inventario.',
                            ]),
                            TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->gt(0)
                            ->rules([
                                'required',
                                'numeric',
                                'min:0.01',
                                function (Get $get) {
                                    return function (string $attribute, $value, callable $fail) use ($get) {
                                        $inventoryId = $get('inventory_id');
                                        if ($inventoryId) {
                                            $inventory = Inventory::find($inventoryId);
                                            if ($inventory && $value > $inventory->quantity) {
                                                $fail("La cantidad no puede ser mayor que la disponible en el inventario ({$inventory->quantity}).");
                                            }
                                        }
                                    };
                                },
                            ])
                            ->validationMessages([
                                'required' => 'La cantidad es obligatoria.',
                                'numeric' => 'La cantidad debe ser un número.',
                                'min' => 'La cantidad debe ser mayor que cero.',
                            ])
                            ->reactive(),
                        DatePicker::make('waste_date')
                            ->label('Fecha de desperdicio')
                            ->native(false)
                            ->required()
                            ->beforeOrEqual(now())
                            ->rules(['required', 'date', 'before_or_equal:today'])
                            ->validationMessages([
                                'required' => 'La fecha de desperdicio es obligatoria.',
                                'date' => 'Debe ingresar una fecha válida.',
                                'before_or_equal' => 'La fecha no puede ser futura.',
                            ]),
                        Select::make('reason')
                            ->label('Razón')
                            ->options([
                                'expired' => 'Vencido',
                                'damaged' => 'Dañado',
                                'overproduction' => 'Sobreproducción',
                                'quality_issues' => 'Problemas de calidad',
                                'other' => 'Otro',
                            ])
                            ->native(false)
                            ->required()
                            ->rules(['required', Rule::in(['expired', 'damaged', 'overproduction', 'quality_issues', 'other'])])
                            ->validationMessages([
                                'required' => 'La razón del desperdicio es obligatoria.',
                                'in' => 'La razón seleccionada no es válida.',
                            ]),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpan('full')
                            ->minLength(2)
                            ->maxLength(500)
                            ->rules([
                                'nullable',
                                'string',
                                'min:2',
                                'max:500',
                                function (Get $get) {
                                    return function (string $attribute, $value, callable $fail) use ($get) {
                                        if ($get('reason') === 'other' && empty($value)) {
                                            $fail('La descripción es obligatoria cuando la razón es "Otro".');
                                        }
                                    };
                                },
                            ])
                            ->validationMessages([
                                'min' => 'La descripción debe tener al menos :min caracteres.',
                                'max' => 'La descripción no puede exceder los :max caracteres.',
                            ]),
                        ]),
                    ]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('inventory.product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label('Cantidad')
                    ->sortable(),
                TextColumn::make('waste_date')
                    ->label('Fecha de desperdicio')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    BadgeColumn::make('reason')
                    ->label('Razón')
                    ->formatStateUsing(function ($state) {
                        $reasons = [
                            'expired' => 'Vencido',
                            'damaged' => 'Dañado',
                            'overproduction' => 'Sobreproducción',
                            'quality_issues' => 'Problemas de calidad',
                            'other' => 'Otro',
                        ];
                        return $reasons[$state] ?? $state;
                    })
                    ->colors([
                        'danger' => 'expired',
                        'warning' => 'damaged',
                        'info' => 'overproduction',
                        'secondary' => 'quality_issues',
                        'primary' => 'other',
                    ]),
                TextColumn::make('description')
                    ->label('Descripción')
                    ->columnSpan('full')
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
                DeleteAction::make()->label('Eliminar')
                ->before(function (DeleteAction $action, Waste $record) {
                    DB::transaction(function () use ($record) {
                        $inventory = Inventory::findOrFail($record->inventory_id);
                        $inventory->quantity += $record->quantity;
                        $inventory->save();
                        InventoryMovement::create([
                            'inventory_id' => $record->inventory_id,
                            'movement_type' => 'input',
                            'quantity' => $record->quantity,
                            'movement_date' => now(),
                            'reference_id' => $record->id,
                            'reference_type' => 'waste_deletion',
                        ]);
                    });
                })
                
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
    

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWastes::route('/'),
            'create' => Pages\CreateWaste::route('/create'),
            'edit' => Pages\EditWaste::route('/{record}/edit'),
        ];
    }
}