<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Filament\Resources\SaleResource\RelationManagers;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use Filament\Tables\Columns\Summarizers\Average;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Get;
class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Ventas';
    protected static ?string $pluralModelLabel = 'ventas';
    protected static ?string $modelLabel = 'Venta';
    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información sobre la venta')->schema([
                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id())
                        ->dehydrated()
                        ->required(),
                    Forms\Components\TextInput::make('total')
                        ->label('Total')
                        ->required()
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->validationMessages([
                            'required' => 'Este campo es requerido.',
                        ]),
                ])
                ->columns(2),
                Forms\Components\Section::make('Realizar venta')
                ->schema([
                    Forms\Components\Repeater::make('saleDetails')
                        ->label('Detalles')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            
                            ->options(function (callable $get, ?Model $record) {
                                $selectedProducts = collect($get('../../saleDetails'))
                                    ->pluck('product_id')
                                    ->filter()
                                    ->toArray();
    
                                if ($record && $record->product_id) {
                                    $selectedProducts = array_diff($selectedProducts, [$record->product_id]);
                                }
    
                                return Product::where('stock', '>', 0)
                                    ->whereNotIn('id', $selectedProducts)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getSearchResultsUsing(function (string $search, callable $get, ?Model $record) {
                                $selectedProducts = collect($get('../../saleDetails'))
                                    ->pluck('product_id')
                                    ->filter()
                                    ->toArray();
    
                                if ($record && $record->product_id) {
                                    $selectedProducts = array_diff($selectedProducts, [$record->product_id]);
                                }
    
                                return Product::where('name', 'like', "%{$search}%")
                                    ->where('stock', '>', 0)
                                    ->whereNotIn('id', $selectedProducts)
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->getOptionLabelUsing(fn ($value): ?string => Product::find($value)?->name)
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload()
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
                            }),                           
                            Forms\Components\TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->rules([
                                'min:1'
                            ])
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                                'min' => 'Este campo debe ser al menos 1.',
                            ])
                            ->disabled(fn (callable $get) => !$get('product_id'))
                            ->rules([
                                'min:1', 
                                function (callable $get) {
                                    return function (string $attribute, $value, callable $fail) use ($get) {
                                        $productId = $get('product_id');
                                        $product = Product::find($productId);
                                        if (!is_numeric($value) || $value < 1) {
                                            $fail("La cantidad debe ser un número entero positivo.");
                                        } elseif ($value > $product->stock) {
                                            $fail("La cantidad no puede ser mayor que el stock disponible ({$product->stock}).");
                                        }
                                    };
                                },
                            ])
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $unitPrice = $get('unit_price') ?? 0;
                                $newQuantity = $state ?? 0;
                                $set('subtotal', $newQuantity * $unitPrice);
                            }),
                            Forms\Components\TextInput::make('unit_price')
                            ->label('Precio unitario')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->disabled()
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->dehydrated(true)
                       
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $quantity = $get('quantity') ?? 0;
                                $unitPrice = $state ?? 0;
                                $set('subtotal', $quantity * $unitPrice);
                            }),
                            Forms\Components\TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->required()
                            ->reactive()
                            ->readonly()
                            ->disabled()
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->dehydrated(true)
                          
                        ])
                        ->columns(4)
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $livewire) {
                            $total = collect($state)->sum(function ($item) {
                                $quantity = floatval($item['quantity'] ?? 0);
                                $unitPrice = floatval($item['unit_price'] ?? 0);
                                return $quantity * $unitPrice;
                            });
                            $set('total', $total);
    
                            $saleId = $livewire->record?->id;
                            if ($saleId) {
                                $totalQuantity = SaleDetail::where('sale_id', $saleId)->sum('quantity');
                                $livewire->record->update(['total_quantity' => $totalQuantity]);
                            }
                        })
                        ->defaultItems(1)
                        ->createItemButtonLabel('Agregar producto')
                        ->maxItems(Product::count())
                        ->disableItemCreation(fn (Get $get): bool => $get('id') !== null)
                        ->deletable(Auth::user()->hasRole('Administrador'))
                        ]),
                    ]);
    }
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Vendedor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de venta')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ult.Actualización')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->prefix('Bs ')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total')
                            ->formatStateUsing(fn (string $state): string => "Bs " . number_format((float)$state, 2, ',', '.'))
                    ]),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Fecha de venta desde'),
                        DatePicker::make('created_until')
                            ->label('Fecha de venta hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar'),
                    DeleteAction::make()
                        ->label('Eliminar')
                        ->before(function (DeleteAction $action, Sale $record) {
                            foreach ($record->saleDetails as $detail) {
                                $product = $detail->product;
                                $product->stock += $detail->quantity;
                                $product->save();
                            }
                        })
                        
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    BulkAction::make('delete')
                        ->label('Eliminar seleccionados')
                        ->color('danger')
                        ->icon('heroicon-o-trash')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (Sale $sale) {
                                foreach ($sale->saleDetails as $detail) {
                                    $product = $detail->product;
                                    $product->stock += $detail->quantity;
                                    $product->save();
                                }
                                $sale->delete();
                            });
    
                            Notification::make()
                                ->title('Ventas eliminadas')
                                ->body('Las ventas seleccionadas han sido eliminadas y el stock ha sido actualizado.')
                                ->success()
                                ->send();
                        })
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
}