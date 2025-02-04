<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\PurchaseDetail;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\DB;
class ProductResource extends Resource 
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Productos';

    protected static ?string $pluralModelLabel = 'Productos';

    protected static ?string $modelLabel = 'Producto';
    
    protected static ?string $navigationGroup = 'Administración';
    
    protected static ?string $recordTitleAttribute = "name";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Producto')
                    ->schema([
                        Grid::make()
                        ->schema([
                            TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignorable: fn ($record) => $record)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                                'unique' => 'El producto ya ha sido registrado.',
                            ])
                            ->columnSpan('full'),
                     
                            TextInput::make('purchase_price')
                                ->label('Precio de Compra')
                                ->numeric()
                                ->minValue(1)
                                ->prefix('Bs '),
                            TextInput::make('sale_price')
                                ->label('Precio de Venta')
                                ->numeric()
                                ->minValue(1)
                                ->prefix('Bs '),
                            Textarea::make('description')
                                ->label('Descripción')
                                ->columnSpan('full')
                                ->minLength(2),
                        ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sale_price')
                    ->label('Precio de Venta')
                    ->prefix('Bs ')
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label('Precio de Compra')
                    ->prefix('Bs ')
                    ->sortable(),
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
            ->actions([
                ViewAction::make()->label('Ver'),
                EditAction::make()->label('Editar'),
                Action::make('delete')
                    ->label('Eliminar')
                    ->visible(fn (Product $record): bool => auth()->user()->can('eliminar productos'))
                    ->requiresConfirmation()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->action(function (Product $record , Action  $action) {
                        $purchaseCount = PurchaseDetail::where('product_id', $record->id)->count();
                        $saleCount = SaleDetail::where('product_id', $record->id)->count();
                    
                        if ($purchaseCount > 0 || $saleCount > 0) {
                            Notification::make()
                                ->title('No se puede eliminar el producto')
                                ->body('Este producto no se puede eliminar porque tiene otros recursos asociados.')
                                ->danger()
                                ->send();
                                $action->cancel(); 
                        }else{
                            
                            $usersToNotify = getUsersByRoles(['Administrador', 'Vendedor']);
                            Notification::make()
                                ->title("Producto eliminado")
                                ->body("El producto {$record->name} ha sido eliminado por el usuario " . auth()->user()->name . ".")
                                ->success()
                                ->sendToDatabase($usersToNotify);  
                            $record->delete();    
                            $action->success();  
                        }
            
                      
                    }),
            ])
            ->filters([
                Filter::make('sale_price')
                    ->form([
                        TextInput::make('sale_price_from')
                            ->label('Precio de venta desde')
                            ->numeric()
                            ->prefix('Bs '),
                        TextInput::make('sale_price_to')
                            ->label('Precio de venta hasta')
                            ->numeric()
                            ->prefix('Bs '),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['sale_price_from'],
                                fn (Builder $query, $price): Builder => $query->where('sale_price', '>=', $price)
                            )
                            ->when(
                                $data['sale_price_to'],
                                fn (Builder $query, $price): Builder => $query->where('sale_price', '<=', $price)
                            );
                    }),
                    Filter::make('created_at')
                        ->form([
                        DatePicker::make('created_from')
                            ->native(false)
                            ->label('Creado desde')
                            ->reactive(), 
                        DatePicker::make('created_until')
                            ->native(false)
                            ->label('Creado hasta')
                            ->reactive(), 
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date)
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date)
                            );
                    })
                    ->label('Filtrar por Fecha de Creación'),
            ]);
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}