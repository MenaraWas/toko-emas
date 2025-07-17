<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // Sync roles setelah user dibuat
        if (isset($this->data['roles']) && is_array($this->data['roles'])) {
            $roles = \Spatie\Permission\Models\Role::whereIn('id', $this->data['roles'])->get();
            $this->record->syncRoles($roles);
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set default status jika tidak ada
        if (!isset($data['status'])) {
            $data['status'] = 'active';
        }

        // Jika bukan owner, pastikan cabang_id diset
        if (!auth()->user()->hasRole('owner')) {
            $data['cabang_id'] = auth()->user()->cabang_id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}