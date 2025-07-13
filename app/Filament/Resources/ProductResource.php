<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;


class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Master Produk';
    protected static ?string $navigationGroup = 'Inventory Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('kode')
                    ->label('Kode Barang')
                    ->default(function () {
                        $prefix = 'EMAS-' . now()->format('Ym');
                        $count = \App\Models\Product::where('kode', 'like', $prefix . '%')->count() + 1;
                        return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
                    })
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->unique(
                        table: 'products',
                        column: 'kode',
                        ignoreRecord: true
                    ),

                Forms\Components\TextInput::make('nama')
                    ->label('Nama Barang')
                    ->required(),

                Forms\Components\TextInput::make('karat')
                    ->label('Karat')
                    ->placeholder('Misal: 24K'),

                Forms\Components\TextInput::make('berat')
                    ->label('Berat (gram)')
                    ->numeric()
                    ->suffix('gram'),

                Forms\Components\TextInput::make('harga_dasar')
                    ->label('Harga Dasar')
                    ->numeric()
                    ->prefix('Rp'),

                SpatieMediaLibraryFileUpload::make('foto')
                    ->collection('produk')
                    ->label('Foto Barang')
                    ->image()
                    ->imageEditor()
                    ->conversion('thumb') // gunakan thumbnail otomatis
                    ->maxFiles(3) // maksimal 3 foto per produk
                    ->required(), // opsional: wajib upload

                Forms\Components\Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                SpatieMediaLibraryImageColumn::make('foto')
                    ->collection('produk')
                    ->conversion('thumb')
                    ->label('Foto')
                    ->circular()
                    ->limit(1),

                Tables\Columns\TextColumn::make('kode')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('karat')
                    ->sortable(),

                Tables\Columns\TextColumn::make('berat')
                    ->label('Berat (gram)'),

                Tables\Columns\TextColumn::make('harga_dasar')
                    ->money('IDR'),

                Tables\Columns\IconColumn::make('aktif')
                    ->boolean()
                    ->label('Aktif'),
            ])
            ->defaultSort('nama')
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}