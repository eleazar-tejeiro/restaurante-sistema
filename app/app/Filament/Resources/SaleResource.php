<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification; 
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;
class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    protected static ?string $navigationLabel = 'Ventas';

    protected static ?string $pluralModelLabel = 'Ventas';
    
    protected static ?string $modelLabel = 'Venta';

    protected static ?string $navigationGroup = 'Administración';


    public static function form(Form $form): Form
    {
        $isEditMode = $form->getOperation() === 'edit';
        
        return $form
        
            ->schema([
                Section::make('Datos generales de la venta')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Hidden::make('user_id')
                                    ->default(auth()->user()->id)
                                    ->required(),
                                TextInput::make('total')
                                    ->required()
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->label('Total')
                                    ->readOnly()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (callable $set, $state) => $set('total', $state))
                                    ->validationMessages([
                                        'required' => 'El total es requerido.',
                                        'numeric' => 'El total debe ser un valor numérico.',
                                    ]),
                            ]),
                    ]),
                Section::make('Detalles de la venta')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Grid::make(3)
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Producto')
                                            ->disabled(fn () => $isEditMode)
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
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('unit_price', $product->sale_price);
                                                    $set('quantity', null);
                                                    $set('subtotal', 0);
                                                } else {
                                                    $set('unit_price', null);
                                                    $set('quantity', null);
                                                    $set('subtotal', null);
                                                }
                                            })
                                            ->native(false)
                                            ->required()
                                            ->searchable()
                                            ->validationMessages([
                                                'required' => 'Debe seleccionar un producto.',
                                            ])
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(fn (Set $set) => $set('quantity', null)),

                                        TextInput::make('quantity')
                                            ->required()
                                            ->numeric()
                                            ->live(onBlur: true)
                                            ->dehydrated()
                                            ->rules([
                                                'min:1',
                                                function (Get $get) {
                                                    return function (string $attribute, $value, callable $fail) use ($get) {
                                                        $productId = $get('product_id');
                                                        if ($productId) {
                                                            $availableStock = Inventory::where('product_id', $productId)->sum('quantity');
                                                            if ($value > $availableStock) {
                                                                $fail("La cantidad no puede ser mayor al stock disponible ({$availableStock}).");
                                                            }
                                                            
                                                        }
                                                    };
                                                },
                                            ])
                                            ->validationMessages([
                                                'required' => 'La cantidad es requerida.',
                                                'numeric' => 'La cantidad debe ser un número.',
                                                'min' => 'La cantidad debe ser al menos 1.',
                                            ])
                                            ->label('Cantidad')
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                $unitPrice = floatval($get('unit_price')) ?? 0;
                                                $quantity = floatval($state) ?? 0;
                                                $subtotal = $unitPrice * $quantity;
                                                $set('subtotal', number_format($subtotal, 2, '.', ''));
                                            }),
                                        TextInput::make('unit_price')
                                            ->required()
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->readOnly()
                                            ->live(onBlur: true)
                                            ->rules(['min:0.01'])
                                            ->validationMessages([
                                                'required' => 'El precio unitario es requerido.',
                                                'numeric' => 'El precio unitario debe ser un número.',
                                                'min' => 'El precio unitario debe ser mayor a 0.',
                                            ])
                                            ->label('Precio unitario')
                                            ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                                $quantity = floatval($get('quantity')) ?? 0;
                                                $unitPrice = floatval($state) ?? 0;
                                                $subtotal = $quantity * $unitPrice;
                                                $set('subtotal', number_format($subtotal, 2, '.', ''));
                                            }),
                                            TextInput::make('subtotal')
                                            ->required()
                                            ->numeric()
                                            ->inputMode('decimal')
                                            ->validationMessages([
                                                'required' => 'El subtotal es requerido.',
                                                'numeric' => 'El subtotal debe ser un número.',
                                            ])
                                            ->readOnly()
                                            ->label('Sub total'),
                                            Hidden::make('is_deleted')
                                            ->default(false),
                                    ]),
                            ])
                            ->createItemButtonLabel('Agregar mas ventas')
                            ->hiddenLabel() 
                            ->minItems(1)
                            ->collapsible()
                            ->deletable(!$isEditMode)  
                            ->addable(!$isEditMode)      
                            ->reorderable(!$isEditMode) 
                            ->itemLabel(fn (array $state): ?string => $state['product_id'] ? Product::find($state['product_id'])?->name : null)

                            ->maxItems(Inventory::count())
                            ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                $total = collect($state)->sum(function ($item) {
                                    if (!($item['is_deleted'] ?? false)) {
                                        $quantity = floatval($item['quantity'] ?? 0);
                                        $unitPrice = floatval($item['unit_price'] ?? 0);
                                        return $quantity * $unitPrice;
                                    }
                                    return 0;
                                });
                                $set('total', number_format($total, 2, '.', ''));
                            })
                            ->validationMessages([
                                'min' => 'Debe agregar al menos un detalle de venta.',
                            ])
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total')
                    ->label('Total')
                    ->prefix('Bs ')
                    ->sortable(),
                TextColumn::make('details_count')
                    ->label('Cantidad de productos')
                    ->alignCenter()
                    ->counts('details')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Fecha de venta')
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
                            ->label('Fecha de venta desde')
                            ->reactive()
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Fecha de venta hasta')
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
                DeleteAction::make()
                    ->label('Eliminar')
                    ->before(function (DeleteAction $action, Sale $record) {
                        $details=$record->details;
                        foreach ($details as $detail) {
                            $productId = $detail->product->id;
                            $inventory = Inventory::where('product_id', $productId)->firstOrFail();
                            $inventory->quantity += $detail->quantity;
                            $inventory->save();     
                            InventoryMovement::create([
                                'inventory_id' => $inventory->id,
                                'movement_type' => 'input',
                                'quantity' => $detail->quantity,
                                'movement_date' => now(),
                                'reference_id' => $record->id,
                                'reference_type' => 'sale_deletion',
                            ]); 
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}