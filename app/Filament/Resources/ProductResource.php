<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\BulkActionGroup;

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
                TextInput::make('kode')
                    ->label('Kode Barang')
                    ->default(function () {
                        $prefix = 'EMAS-' . now()->format('Ym');
                        $count = Product::where('kode', 'like', $prefix . '%')->count() + 1;
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

                TextInput::make('nama')
                    ->label('Nama Barang')
                    ->required(),

                TextInput::make('karat')
                    ->label('Karat')
                    ->placeholder('Misal: 24K'),

                TextInput::make('berat')
                    ->label('Berat (gram)')
                    ->numeric()
                    ->suffix('gram'),

                TextInput::make('harga_dasar')
                    ->label('Harga Dasar')
                    ->numeric()
                    ->prefix('Rp'),

                SpatieMediaLibraryFileUpload::make('foto')
                    ->collection('produk')
                    ->label('Foto Barang')
                    ->image()
                    ->imageEditor()
                    ->conversion('thumb')
                    ->maxFiles(3)
                    ->required(),

                Toggle::make('aktif')
                    ->label('Aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
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
            ->filters([])
            ->actions([
                EditAction::make()
                    ->visible(fn () => Auth::user()?->can('product.edit')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->can('product.delete')),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    // Tampilkan hanya jika user punya permission view
    public static function shouldRegisterNavigation(): bool
    {
        return Auth::check() && Auth::user()->can('product.view');
    }

    // Batasi akses halaman tertentu berdasarkan permission
    public static function canViewAny(): bool
    {
        return Auth::check() && Auth::user()->can('product.view');
    }

    public static function canCreate(): bool
    {
        return Auth::check() && Auth::user()->can('product.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::check() && Auth::user()->can('product.edit');
    }

    public static function canDelete($record): bool
    {
        return Auth::check() && Auth::user()->can('product.delete');
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
