<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Role Management';
    protected static ?string $pluralModelLabel = 'Roles';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Role Information')
                    ->schema([
                        Select::make('role_type')
                            ->label('Tipe Role')
                            ->options([
                                'pusat' => 'Role Pusat',
                                'cabang' => 'Role Cabang',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                // Update preview saat role_type berubah
                                $roleName = $get('role_name');
                                if ($state && $roleName) {
                                    $set('name_preview', $state . '.' . $roleName);
                                }
                            })
                            ->helperText('Pilih apakah role ini untuk pusat atau cabang.'),

                        TextInput::make('role_name')
                            ->label('Nama Role')
                            ->required()
                            ->maxLength(50)
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                // Update preview saat role_name berubah
                                $roleType = $get('role_type');
                                if ($roleType && $state) {
                                    $set('name_preview', $roleType . '.' . $state);
                                }
                            })
                            ->helperText('Hanya nama tanpa prefix. Misal: admin_keuangan')
                            ->placeholder('admin_keuangan'),

                        TextInput::make('name_preview')
                            ->label('Preview Role Name')
                            ->disabled()
                            ->helperText('Preview nama role yang akan dibuat')
                            ->placeholder('Akan otomatis terisi'),
                    ]),

                Section::make('Permissions')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Permissions')
                            ->options(
                                \Spatie\Permission\Models\Permission::all()->pluck('name', 'name')
                            )
                            ->columns(2)
                            ->searchable()
                            ->bulkToggleable()
                            ->helperText('Centang permission yang ingin diberikan.')
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable()
                    ->label('Role Name')
                    ->weight('bold')
                    ->badge()
                    ->color(function ($record) {
                        if (str_starts_with($record->name, 'pusat.')) {
                            return 'info';
                        } elseif (str_starts_with($record->name, 'cabang.')) {
                            return 'warning';
                        }
                        return 'gray';
                    }),
                
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions Count')
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_type')
                    ->label('Tipe Role')
                    ->options([
                        'pusat' => 'Role Pusat',
                        'cabang' => 'Role Cabang',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->where('name', 'like', $value . '.%')
                        );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    // Authorization methods
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->can('role.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->can('role.create');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->can('role.update');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->can('role.delete');
    }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->can('role.view');
    }

    // Global search
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }
}