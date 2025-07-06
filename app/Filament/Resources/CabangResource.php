<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CabangResource\Pages;
use App\Filament\Resources\CabangResource\RelationManagers;
use App\Models\Cabang;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CabangResource extends Resource
{
    protected static ?string $model = Cabang::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Cabang Toko';
    protected static ?string $pluralModelLabel = 'Cabang';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
                TextInput::make('kode')
                    ->required()
                    ->maxLength(50),
                TextInput::make('alamat')
                    ->maxLength(255),
                TextInput::make('telepon')
                    ->maxLength(50),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')->searchable()->sortable(),
                TextColumn::make('kode')->sortable(),
                TextColumn::make('alamat'),
                TextColumn::make('telepon'),
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
            'index' => Pages\ListCabangs::route('/'),
            'create' => Pages\CreateCabang::route('/create'),
            'edit' => Pages\EditCabang::route('/{record}/edit'),
        ];
    }

    // Proteksi akses dengan pengecekan null safety
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->can('view_cabangs');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->can('create_cabangs');
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && $user->can('update_cabangs');
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && $user->can('delete_cabangs');
    }
}