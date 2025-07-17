<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Filament\Resources\RoleResource\RelationManagers;
use Spatie\Permission\Models\Role;
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
use Illuminate\Database\Eloquent\Collection;

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
        $isOwner = auth()->user()?->hasRole('owner') ?? false;
        
        return $form
            ->schema([
                Section::make('Role Information')
                    ->schema([
                        Select::make('role_type')
                            ->label('Tipe Role')
                            ->options(function () use ($isOwner) {
                                $options = [];
                                if ($isOwner) {
                                    $options['pusat'] = 'Role Pusat';
                                }
                                $options['cabang'] = 'Role Cabang';
                                return $options;
                            })
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $get, $set) {
                                // Update preview saat role_type berubah
                                $roleName = $get('role_name');
                                if ($state && $roleName) {
                                    $set('name_preview', $state . '.' . $roleName);
                                }
                            })
                            ->helperText('Pilih apakah role ini untuk pusat atau cabang.')
                            ->default(function () use ($isOwner) {
                                return $isOwner ? null : 'cabang';
                            })
                            ->disabled(fn ($context) => $context === 'edit'), // Disable saat edit

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
                            ->rules([
                                function ($get) {
                                    return function (string $attribute, $value, \Closure $fail) use ($get) {
                                        $roleType = $get('role_type');
                                        if ($roleType && $value) {
                                            $fullName = $roleType . '.' . $value;
                                            $existingRole = Role::where('name', $fullName)->first();
                                            if ($existingRole) {
                                                $fail('Role dengan nama "' . $fullName . '" sudah ada.');
                                            }
                                        }
                                    };
                                }
                            ])
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
                            ->options(function () use ($isOwner) {
                                $permissions = \Spatie\Permission\Models\Permission::all();
                                
                                if (!$isOwner) {
                                    // Non-owner hanya bisa assign permission cabang
                                    $permissions = $permissions->filter(function ($permission) {
                                        return str_contains($permission->name, 'cabang') || 
                                               !str_contains($permission->name, 'pusat');
                                    });
                                }
                                
                                return $permissions->pluck('name', 'name');
                            })
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
                    })
                    ->formatStateUsing(function ($state) {
                        // Format tampilan role name
                        if (str_starts_with($state, 'pusat.')) {
                            return 'Pusat: ' . ucfirst(str_replace('pusat.', '', $state));
                        } elseif (str_starts_with($state, 'cabang.')) {
                            return 'Cabang: ' . ucfirst(str_replace('cabang.', '', $state));
                        }
                        return ucfirst($state);
                    }),
                
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label('Permissions Count')
                    ->badge()
                    ->color('success'),
                
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Users Count')
                    ->badge()
                    ->color('primary'),
                
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
                Tables\Actions\EditAction::make()
                    ->visible(fn (Model $record): bool => static::canEdit($record)),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (Model $record): bool => static::canDelete($record))
                    ->action(function (Model $record) {
                        // Cek apakah role masih digunakan
                        if ($record->users()->count() > 0) {
                            throw new \Exception('Role tidak dapat dihapus karena masih digunakan oleh user.');
                        }
                        $record->delete();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                if (!static::canDelete($record)) {
                                    throw new \Exception("You don't have permission to delete role: {$record->name}");
                                }
                                if ($record->users()->count() > 0) {
                                    throw new \Exception("Role {$record->name} cannot be deleted because it's still in use.");
                                }
                            }
                            $records->each->delete();
                        }),
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
        if (!$user || !$user->can('role.update')) {
            return false;
        }
        
        // Owner bisa edit semua role
        if ($user->hasRole('owner')) {
            return true;
        }
        
        // Non-owner hanya bisa edit role cabang
        return str_starts_with($record->name, 'cabang.');
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user || !$user->can('role.delete')) {
            return false;
        }
        
        // Tidak bisa delete role sistem
        $systemRoles = ['owner', 'super_admin'];
        if (in_array($record->name, $systemRoles)) {
            return false;
        }
        
        // Owner bisa delete semua role (kecuali sistem)
        if ($user->hasRole('owner')) {
            return true;
        }
        
        // Non-owner hanya bisa delete role cabang
        return str_starts_with($record->name, 'cabang.');
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

    // Query modifier untuk filter berdasarkan role user
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && !$user->hasRole('owner')) {
            // Non-owner hanya bisa lihat role cabang
            $query->where('name', 'like', 'cabang.%');
        }

        return $query;
    }
}