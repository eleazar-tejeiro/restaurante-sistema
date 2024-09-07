<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WasteResource\Pages;
use App\Filament\Resources\WasteResource\RelationManagers;
use App\Models\Waste;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Collection;
class WasteResource extends Resource
{
    protected static ?string $model = Waste::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Desperdicios';
    protected static ?string $pluralModelLabel = 'Desperdicios';
    protected static ?string $modelLabel = 'Desperdicio';
    public static function getNavigationGroup(): ?string
    {
        return 'Administración';
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Desperdicio')
                    ->schema([
                        Forms\Components\Select::make('product_id')
                            ->relationship('product', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->label('Producto')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('max_quantity', Product::find($state)?->stock ?? 0);
                                $set('quantity', null);
                            })
                            ->columnSpan(2),
                            Forms\Components\TextInput::make('quantity')
                            ->required()
                            ->integer()
                            ->minValue(1)
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                    
                            ->label('Cantidad')
                            ->reactive()
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
                            ->disabled(fn (callable $get) => !$get('product_id')),
                        Forms\Components\DatePicker::make('wasted_date')
                            ->required()
                            ->label('Fecha de Desecho')
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->default(now()),
                        Forms\Components\Textarea::make('reason')
                            ->required()
                            ->maxLength(255)
                            ->label('Motivo')
                            ->validationMessages([
                                'required' => 'Este campo es requerido.',
                            ])
                            ->columnSpan(2),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('product.name')
                ->label('Producto')
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('quantity')
                ->label('Cantidad')
                ->sortable(),
            Tables\Columns\TextColumn::make('wasted_date')
                ->label('Fecha de Desecho')
                ->date('d/m/Y')
                ->sortable(),
            Tables\Columns\TextColumn::make('reason')
                ->label('Motivo')
                ->limit(30),
        ])
        ->filters([
            Filter::make('wasted_date')
                ->form([
                    Forms\Components\DatePicker::make('from')
                        ->label('Desde'),
                    Forms\Components\DatePicker::make('until')
                        ->label('Hasta'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['from'],
                            fn (Builder $query, $date): Builder => $query->whereDate('wasted_date', '>=', $date),
                        )
                        ->when(
                            $data['until'],
                            fn (Builder $query, $date): Builder => $query->whereDate('wasted_date', '<=', $date),
                        );
                })
        ])
        ->actions([
            ActionGroup::make([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\EditAction::make()->label('Editar'),
                DeleteAction::make()
                    ->label('Eliminar')
                    ->before(function (Waste $record) {
                        $product = Product::find($record->product_id);
                        if ($product) {
                            $product->stock += $record->quantity;
                            $product->save();
                        }
                    }),
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
                        $records->each(function (Waste $waste) {
                            $product = Product::find($waste->product_id);
                            if ($product) {
                                $product->stock += $waste->quantity;
                                $product->save();
                            }
                            $waste->delete();
                        });

                        Notification::make()
                            ->title('Desperdicios eliminados')
                            ->body('Los desperdicios seleccionados han sido eliminados y el stock ha sido actualizado.')
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
            'index' => Pages\ListWastes::route('/'),
            'create' => Pages\CreateWaste::route('/create'),
            'edit' => Pages\EditWaste::route('/{record}/edit'),
        ];
    }
}