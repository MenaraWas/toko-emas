<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use App\Models\Cabang;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Closure;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Karyawan Management';
    protected static ?string $pluralModelLabel = 'User';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        // Cek role user yang sedang login
        $isOwner = auth()->user()?->hasRole('owner') ?? false;

        // Siapkan field untuk bagian Assignment secara dinamis
        $assignmentFields = [];

        // Jika user adalah owner, tampilkan dropdown pilihan cabang
        if ($isOwner) {
            $assignmentFields[] = Select::make('cabang_id')
                ->label('Cabang')
                ->options(Cabang::all()->pluck('nama', 'id'))
                ->required()
                ->searchable()
                ->placeholder('Pilih cabang');
        } 
        // Jika bukan owner (misal: admin cabang), gunakan hidden field
        else {
            $assignmentFields[] = Forms\Components\Hidden::make('cabang_id')
                ->default(auth()->user()?->cabang_id);
        }
        
        // Tambahkan field 'roles' yang selalu ada
        $assignmentFields[] = Select::make('roles')
            ->label('Role')
            ->multiple()
            ->options(function () use ($isOwner) {
                $query = \Spatie\Permission\Models\Role::query();

                if (!$isOwner) {
                    // hanya role cabang
                    $query->where('name', 'like', 'cabang.%');
                }

                return $query->get()->mapWithKeys(function ($role) {
                    // tampilkan label lebih rapi
                    $label = match(true) {
                        str_starts_with($role->name, 'cabang.') => 'Cabang: ' . ucfirst(str_replace('cabang.', '', $role->name)),
                        str_starts_with($role->name, 'pusat.') => 'Pusat: ' . ucfirst(str_replace('pusat.', '', $role->name)),
                        default => ucfirst($role->name)
                    };
                    return [$role->id => $label];
                });
            })
            ->required()
            ->searchable()
            ->placeholder('Select roles')
            ->rules([
                function () {
                    return function (string $attribute, $value, \Closure $fail) {
                        if (!auth()->user()->hasRole('owner')) {
                            $invalidRoles = collect($value)->filter(function ($roleId) {
                                $role = \Spatie\Permission\Models\Role::find($roleId);
                                return !str_starts_with($role->name, 'cabang.');
                            });
                            
                            if ($invalidRoles->isNotEmpty()) {
                                $fail('You can only assign branch-level roles.');
                            }
                        }
                    };
                }
            ]);

        return $form
            ->schema([
                Section::make('User Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter user name'),
                        
                        TextInput::make('email')
                            ->required()
                            ->email()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Enter email address'),
                        
                        TextInput::make('phone')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('Enter phone number'),
                    ]),

                Section::make('Assignment')
                    ->schema($assignmentFields),

                Section::make('Status & Security')
                    ->schema([
                                                
                        TextInput::make('password')
                            ->password()
                            ->maxLength(255)
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : null)
                            ->required(fn($context) => $context === 'create')
                            ->dehydrated(fn($state) => !empty($state))
                            ->revealable()
                            ->placeholder('Enter password')
                            ->helperText('Leave blank to keep current password (when editing)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-m-envelope'),
                
                TextColumn::make('phone')
                    ->searchable()
                    ->icon('heroicon-m-phone')
                    ->placeholder('No phone'),
                
                TextColumn::make('cabang.nama')
                    ->label('Cabang')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(function ($state) {
                        return match(true) {
                            str_starts_with($state, 'cabang.') => 'Cabang: ' . ucfirst(str_replace('cabang.', '', $state)),
                            str_starts_with($state, 'pusat.') => 'Pusat: ' . ucfirst(str_replace('pusat.', '', $state)),
                            default => ucfirst($state)
                        };
                    }),
                
                                
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->filters([
                Tables\Filters\SelectFilter::make('cabang_id')
                    ->label('Cabang')
                    ->options(Cabang::all()->pluck('nama', 'id'))
                    ->visible(fn () => Auth::user() && Auth::user()->hasRole('owner')),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'suspended' => 'Suspended'
                    ]),
                
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->label('Role')
                    ->multiple()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->visible(fn (Model $record): bool => static::canDelete($record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            // Custom validation before bulk delete
                            $records->each(function ($record) {
                                if (!static::canDelete($record)) {
                                    throw new \Exception("You don't have permission to delete {$record->name}");
                                }
                            });
                            $records->each->delete();
                        }),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    // Authorization methods dengan null safety
    public static function canViewAny(): bool
    {
        $user = Auth::user();
        return $user && $user->can('user.view');
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();
        return $user && $user->can('user.create');
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();
        if (!$user || !$user->can('user.update')) {
            return false;
        }
        
        // Owner bisa edit semua
        if ($user->hasRole('owner')) {
            return true;
        }
        
        // Non-owner hanya bisa edit user di cabang yang sama
        return $record->cabang_id === $user->cabang_id;
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        if (!$user || !$user->can('user.delete')) {
            return false;
        }
        
        // Tidak bisa delete diri sendiri
        if ($record->id === $user->id) {
            return false;
        }
        
        // Owner bisa delete semua (kecuali diri sendiri)
        if ($user->hasRole('owner')) {
            return true;
        }
        
        // Non-owner hanya bisa delete user di cabang yang sama
        return $record->cabang_id === $user->cabang_id;
    }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();
        return $user && $user->can('user.view');
    }

    // Global search
    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->name;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = Auth::user();

        if ($user && !$user->hasRole('owner')) {
            // Jika bukan owner, filter berdasarkan cabangnya sendiri
            $query->where('cabang_id', $user->cabang_id);
        } elseif ($user && $user->hasRole('owner') && session('active_branch_id')) {
            // Jika owner dan memilih cabang tertentu di session
            $query->where('cabang_id', session('active_branch_id'));
        }

        return $query;
    }
}