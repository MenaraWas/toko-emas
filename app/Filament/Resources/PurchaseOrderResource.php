<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseOrderResource\Pages;
use App\Filament\Resources\PurchaseOrderResource\RelationManagers;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = "Inventory Management";

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                 TextInput::make('kode')
                ->label('Kode PO')
                ->default(function () {
                    $prefix = 'PO-' . now()->format('Ym');
                    $count = PurchaseOrder::where('kode', 'like', $prefix.'%')->count() + 1;
                    return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
                })
                ->disabled()
                ->dehydrated()
                ->unique(ignoreRecord: true),

            TextInput::make('supplier_name')
                ->label('Supplier / Pabrik')
                ->required(),

            DatePicker::make('tanggal_order')
                ->label('Tanggal Order')
                ->default(now())
                ->required(),

            Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'sent' => 'Dikirim ke Supplier',
                    'received' => 'Diterima',
                    'cancelled' => 'Dibatalkan',
                ])
                ->default('draft')
                ->required(),

            TextInput::make('catatan')
                ->label('Catatan')
                ->columnSpanFull(),
            
            Forms\Components\Hidden::make('created_by')
                ->default(auth()->id()),

            Repeater::make('items')
                ->relationship()
                ->label('Item Pesanan')
                ->schema([
                    Select::make('product_id')
                        ->label('Produk')
                        ->relationship('product', 'nama')
                        ->searchable()
                        ->required(),

                    TextInput::make('kuantitas')
                        ->numeric()
                        ->label('Qty')
                        ->default(1)
                        ->required(),

                    TextInput::make('harga_satuan')
                        ->numeric()
                        ->label('Harga Satuan (Rp)')
                        ->required(),

                    TextInput::make('total_harga')
                        ->numeric()
                        ->label('Subtotal')
                        ->dehydrated()
                        ->disabled()
                        ->default(0)
                        ->afterStateHydrated(function ($state, callable $set, $get) {
                            // Hitung total saat edit
                            $set('total_harga', $get('kuantitas') * $get('harga_satuan'));
                        })
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, $get) {
                            // Hitung total saat input berubah
                            $set('total_harga', $get('kuantitas') * $get('harga_satuan'));
                        }),
                ])
                ->createItemButtonLabel('Tambah Item')
                ->columns(4)
                ->required(),
            ]);
            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                 TextColumn::make('kode')
                ->sortable()
                ->searchable(),

                TextColumn::make('supplier_name')
                    ->label('Supplier'),

                TextColumn::make('tanggal_order')
                    ->date()
                    ->label('Tanggal'),

                BadgeColumn::make('status')
                    ->colors([
                        'primary' => 'draft',
                        'info' => 'sent',
                        'success' => 'received',
                        'danger' => 'cancelled',
                    ])
                    ->label('Status'),
            ])
            ->defaultSort('tanggal_order', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
