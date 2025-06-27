<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';

    public static function form(Form $form): Form
    {
        
        $user = auth()->user();

        return $form
            ->schema([
                //
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->required()
                    ->email()
                    ->unique(ignoreRecord:true),
                Select::make('role_id')
                    ->label('Role')
                    ->required()
                    ->options(function () {
                        $user = auth()->user();

                        if (!$user) {
                            return [];
                        }

                        if (in_array($user->role->name, ['Owner', 'SuperAdmin'])) {
                            // Owner & SuperAdmin bisa assign semua role
                            return \App\Models\Role::pluck('name', 'id');
                        }

                        if ($user->role->name === 'Admin Branch') {
                            // Admin Branch hanya boleh assign Admin Branch dan Kasir
                            return \App\Models\Role::whereIn('name', ['Admin Branch', 'Kasir'])->pluck('name', 'id');
                        }

                        // Role lain tidak boleh assign apa-apa
                        return [];
                    }),
                Select::make('branch_id')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->required()
                    ->visible(fn () => in_array($user->role->name, ['Owner', 'SuperAdmin'])),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                TextInput::make('password')
                        ->password()
                        ->dehydrateStateUsing(fn($state)=> filled($state) ? Hash::make($state):null)
                        ->required(fn (string $context): bool => $context === 'create')
                        ->maxLength(255),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('role.name')->label('Role'),
                TextColumn::make('branch.name')->label('Branch'),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'active',
                        'danger' => 'inactive',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        // Owner dan SuperAdmin selalu bisa
        if (in_array($user->role->name, ['Owner', 'SuperAdmin'])) {
            return true;
        }

        // Admin Branch hanya boleh lihat
        if ($user->role->name === 'Admin Branch') {
            return true;
        }

        // Lainnya (Kasir, Admin Gudang) tidak bisa
        return false;
    }

     public static function canView(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if (in_array($user->role->name, ['Owner', 'SuperAdmin'])) {
            return true;
        }

        if ($user->role->name === 'Admin Branch') {
            // Tidak boleh lihat Owner/SuperAdmin
            if (in_array($record->role->name, ['Owner', 'SuperAdmin'])) {
                return false;
            }
            return $record->branch_id === $user->branch_id;
        }

        return false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        return in_array($user->role->name, ['Owner', 'SuperAdmin', 'Admin Branch']);
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        if (in_array($user->role->name, ['Owner', 'SuperAdmin'])) {
            return true;
        }

        if ($user->role->name === 'Admin Branch') {
            // Tidak boleh edit Owner/SuperAdmin
            if (in_array($record->role->name, ['Owner', 'SuperAdmin'])) {
                return false;
            }
            return $record->branch_id === $user->branch_id;
        }

        return false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        // Hanya Owner yang bisa hapus user
        return $user->role->name === 'Owner';
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();
        if (!$user) return false;

        return in_array($user->role->name, ['Owner', 'SuperAdmin', 'Admin Branch']);
    }

    public static function beforeSave(Model $record): void
    {
        $user = auth()->user();
        if ($user->role->name === 'Admin Branch') {
            if ($record->branch_id !== $user->branch_id) {
                abort(403, 'Anda tidak boleh mengedit user di cabang lain.');
            }
        }
    }

    // Filter query agar Admin Branch hanya melihat cabangnya sendiri
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if ($user->role->name === 'Admin Branch') {
            return $query->where('branch_id', $user->branch_id)
                ->where('role_id', '!=', 1) // Tidak bisa melihat Owner
                ->where('role_id', '!=', 2); // Tidak bisa melihat SuperAdmin
        }

        return $query;
    }
    
}
