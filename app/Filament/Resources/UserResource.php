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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

                return $query
                    ->get()
                    ->mapWithKeys(function ($role) {
                        // tampilkan label lebih rapi
                        $label = ucfirst(str_replace(['cabang.', 'pusat.'], '', $role->name));
                        return [$role->id => $label];
                    });
            })
            ->required()
            ->searchable()
            ->placeholder('Select roles');

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
                    ]),

                Section::make('Assignment')
                    ->schema($assignmentFields),

                Section::make('Security')
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
                
                TextColumn::make('cabang.nama')
                    ->label('Cabang')
                    ->sortable()
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->color('success'),
                
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
        return $user &&
            $user->can('user.update') &&
            ($user->hasRole('owner') || $record->cabang_id === $user->cabang_id);
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();
        return $user && 
            $user->can('user.delete') &&
            ($user->hasRole('owner') || $record->cabang_id === $user->cabang_id);
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
        return ['name', 'email'];
    }

    // Fixed: Menghapus duplikasi method getEloquentQuery dan menggabungkan logikanya
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