<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'Productos';
    protected static ?string $pluralModelLabel = 'Productos';
    protected static ?string $modelLabel = 'Producto';
    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                                'unique' => 'El producto ya ha sido registrado.',

                            ])
                            ->columnSpan('full'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('purchase_price')
                                    ->label('Precio de compra')
                                    ->numeric()
                                    ->required()
                                    ->validationMessages([
                                        'required' => 'Este campo es requerido.',
                                        'min' => 'Este campo debe ser al menos 1.',
                                    ])
                                    ->rules([
                                        'min:1'
                                    ])
                                    ->prefix('Bs'),
                                Forms\Components\TextInput::make('sale_price')
                                    ->label('Precio de venta')
                                    ->numeric()
                                    ->rules([
                                        'min:1'
                                    ])
                                    ->validationMessages([
                                        'required' => 'Este campo es requerido.',
                                        'min' => 'Este campo debe ser al menos 1.',
                                    ])
                                    ->required()
                                    ->prefix('Bs'),
                            ]),
                        Forms\Components\TextInput::make('stock')
                            ->label('Existencias')
                            ->numeric()
                            ->required()
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                                'min' => 'Este campo debe ser al menos 1.',
                            ])
                            ->rules([
                                'min:1'
                            ])
                            ->minValue(0),
                        Forms\Components\DatePicker::make('expiration_date')
                            ->label('Fecha de vencimiento'),
                        Forms\Components\Textarea::make('description')
                            ->label('Descripción')
                            ->columnSpan('full'),
                      
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Precio de compra')
                    ->prefix('Bs '),
                Tables\Columns\TextColumn::make('sale_price')
                    ->label('Precio de venta')
                    ->prefix('Bs ')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->label('Existencias')
                    ->sortable(),
                Tables\Columns\TextColumn::make('expiration_date')
                    ->label('Fecha de vencimiento')
                    ->searchable()
                    ->date(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Ult.Actualización')
                    ->dateTime()
                    ->sortable()
            ])
            ->filters([
                Filter::make('expiration_date')
                    ->form([
                        Forms\Components\DatePicker::make('expiration_from')
                            ->label('Fecha de vencimiento desde'),
                        Forms\Components\DatePicker::make('expiration_to')
                            ->label('Fecha de vencimiento hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['expiration_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expiration_date', '>=', $date),
                            )
                            ->when(
                                $data['expiration_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('expiration_date', '<=', $date),
                            );
                    })
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()->label('Ver'),
                    Tables\Actions\EditAction::make()->label('Editar'),
                    Tables\Actions\DeleteAction::make()->label('Eliminar'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('Eliminar seleccionados'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}