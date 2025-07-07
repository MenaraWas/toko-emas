<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PermissionResource\Pages;
use App\Filament\Resources\PermissionResource\RelationManagers;
use Spatie\Permission\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $navigationLabel = 'Permission';
    protected static ?string $pluralModelLabel = 'Permissions';
    protected static ?string $navigationGroup = 'User Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->placeholder('Contoh: view_users, create_posts, etc.')
                    ->helperText('Gunakan format: action_resource (contoh: view_users, create_posts)'),
                
                Select::make('guard_name')
                    ->label('Guard')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ])
                    ->default('web')
                    ->required(),
                
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Permission Name')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Permission name copied!')
                    ->copyMessageDuration(1500),
                
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'web' => 'success',
                        'api' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('guard_name')
                    ->label('Guard')
                    ->options([
                        'web' => 'Web',
                        'api' => 'API',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Permission')
                        ->modalDescription('Apakah Anda yakin ingin menghapus permission yang dipilih? Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Hapus'),
                ]),
            ])
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Buat Permission Pertama'),
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
            'index' => Pages\ListPermissions::route('/'),
            'create' => Pages\CreatePermission::route('/create'),
            'edit' => Pages\EditPermission::route('/{record}/edit'),
        ];
    }

    // Proteksi akses
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('owner') || $user->can('view.permissions'));
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('owner') || $user->can('create.permissions'));
    }

    public static function canEdit($record): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('owner') || $user->can('update.permissions'));
    }

    public static function canDelete($record): bool
    {
        $user = Auth::user();
        return $user && ($user->hasRole('owner') || $user->can('delete.permissions'));
    }

    // Method untuk mendapatkan permission yang umum digunakan
    public static function getCommonPermissions(): array
    {
        return [
            // User Management
            'view.users' => 'Lihat Users',
            'create.users' => 'Buat Users',
            'update.users' => 'Update Users',
            'delete.users' => 'Hapus Users',
            
            // Role Management
            'view.roles' => 'Lihat Roles',
            'create.roles' => 'Buat Roles',
            'update.roles' => 'Update Roles',
            'delete.roles' => 'Hapus Roles',
            
            // Permission Management
            'view.permissions' => 'Lihat Permissions',
            'create.permissions' => 'Buat Permissions',
            'update.permissions' => 'Update Permissions',
            'delete.permissions' => 'Hapus Permissions',
            
            // Cabang Management
            'view.cabangs' => 'Lihat Cabang',
            'create.cabangs' => 'Buat Cabang',
            'update.cabangs' => 'Update Cabang',
            'delete.cabangs' => 'Hapus Cabang',
        ];
    }
}