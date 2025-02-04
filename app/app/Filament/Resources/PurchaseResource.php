<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\InventoryMovement;
use App\Models\PurchaseDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Actions\Action;
use Closure;

class PurchaseResource extends Resource
{
    
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $modelLabel = 'Compra';

    protected static ?string $pluralModelLabel = 'Compras';
    
    protected static ?string $navigationGroup = 'Administración';

    
    
    public static function form(Form $form): Form
    {
        $isEditMode = $form->getOperation() === 'edit';
        return $form
            ->schema([
                Section::make('Datos generales de la compra')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Hidden::make('user_id')
                                    ->default(auth()->user()->id) 
                                    ->required(),
                                TextInput::make('batch')
                                    ->label('Nº de factura'),
                                Select::make('supplier_id')
                                    ->relationship('supplier', 'name')
                                    ->required()
                                    ->label('Proveedor')
                                    ->validationMessages([
                                        'required' => 'Debe seleccionar un proveedor.',
                                    ])
                                    ->searchable()
                                    ->native(false),
                                TextInput::make('total')
                                    ->required()
                                    ->numeric()
                                    ->inputMode('decimal')
                                    ->label('Total')
                                    ->readOnly()
                                    ->dehydrated()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (Set $set, $state) => $set('total', $state))
                                    ->validationMessages([
                                        'required' => 'El total es requerido.',
                                        'numeric' => 'El total debe ser un valor numérico.',
                                    ]),
                            ]),
                    ]),
    
                Section::make('Detalles de la compra')
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
                                                
                                                return Product::whereNotIn('id', $selectedProducts)
                                                    ->pluck('name', 'id');
                                            })
                                            ->afterStateUpdated(function ($state, callable $set) {
                                                $product = Product::find($state);
                                                if ($product) {
                                                    $set('unit_price', $product->purchase_price);
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
                                            ->disabled(fn (Get $get) => $get('is_excluded'))  
                                            ->helperText(fn (Get $get) => $get('is_excluded') ? 'Este campo está deshabilitado porque ya vencio el producto.' : null)
                                            ->rules(['min:1'])
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
                                        DatePicker::make('expiration_date')
                                            ->label('Fecha de vencimiento')
                                            ->rules(['date', 'after:today'])
                                            ->validationMessages([
                                                'date' => 'Debe ingresar una fecha válida.',
                                                'after' => 'La fecha de vencimiento debe ser posterior a hoy.',
                                            ])
                                            ->native(false),
                                    ]),
                            ])
                            ->columns(2)
                            ->columnSpanFull()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $livewire) {
                                $total = collect($state)->sum(function ($item) {
                                    $quantity = floatval($item['quantity'] ?? 0);
                                    $unitPrice = floatval($item['unit_price'] ?? 0);
                                    return $quantity * $unitPrice;
                                });
                                $set('total', number_format($total, 2, '.', ''));
                            })
                            ->createItemButtonLabel('Agregar mas compras')
                            ->hiddenLabel() 
                            ->deletable(!$isEditMode)  
                            ->addable(!$isEditMode)      
                            ->reorderable(!$isEditMode) 
                            ->minItems(1)
                            ->itemLabel(fn (array $state): ?string => $state['product_id'] ? Product::find($state['product_id'])?->name : null)
                            ->validationMessages([
                                'min' => 'Debe agregar al menos un detalle de compra.',
                            ])
                    ])
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')
        ->columns([
            TextColumn::make('supplier.name')
                ->label('Proveedor')
                ->searchable()
                ->sortable(),
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
                ->label('Fecha de compra')
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
                            ->label('Fecha de compra desde')
                            ->reactive()
                            ->native(false),
                        DatePicker::make('until')
                            ->label('Fecha de compra hasta')
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
                ->visible(fn (Purchase $record): bool => auth()->user()->can('eliminar productos'))
                ->action(function (Purchase $record , Action  $action) {
                    $purchaseDetails = PurchaseDetail::where('product_id', $record->id);
                    foreach ($record->details as $detail) {
                        $inventory = Inventory::where('product_id', $detail->product_id)->first();
                        if (!$inventory || $inventory->quantity < $detail->quantity) {
                            Notification::make()
                                ->title('Error al eliminar')
                                ->body("No se puede eliminar la compra porque el producto '{$detail->product->name}' no tiene suficiente stock en inventario.")
                                ->danger()
                                ->send();
                            $action->cancel();
                            return ;
                        }else{
                            $inventory->decrement('quantity', $detail->quantity);
                            InventoryMovement::create([
                                'inventory_id' => $inventory->id,
                                'movement_type' => 'output',
                                'quantity' => $detail->quantity,
                                'reference_id' => $record->id,
                                'reference_type' => 'purchase_deletion',
                                'movement_date' => now(),
                            ]);
                        }
                    }
                    $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
                    Notification::make()
                    ->title('Borrado')
                    ->success()
                    ->send();
                    Notification::make()
                        ->title("Compra eliminada")
                        ->body("La compra #{$record->id} ha sido eliminada por el usuario " . auth()->user()->name . ".")
                        ->success()
                        ->sendToDatabase($usersToNotify);
                    $purchaseDetails->delete();
                    $record->delete();
                })
        ]);

    }
    protected function notifyUsers($actionType)
    {
        $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);

        Notification::make()
            ->title("Compra {$actionType}")
            ->body("La compra #{$this->record->id} ha sido {$actionType} por el usuario " . auth()->user()->name . ".")
            ->success()
            ->sendToDatabase($usersToNotify);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }
}