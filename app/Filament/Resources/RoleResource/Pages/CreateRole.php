<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Gabungkan role_type dan role_name menjadi name
        if (isset($data['role_type']) && isset($data['role_name'])) {
            $data['name'] = $data['role_type'] . '.' . $data['role_name'];
        }

        // Jika bukan owner, pastikan role_type adalah cabang
        if (!auth()->user()->hasRole('owner')) {
            $data['role_type'] = 'cabang';
            $data['name'] = 'cabang.' . $data['role_name'];
        }

        // Hapus field yang tidak perlu disimpan
        // Hapus field yang tidak perlu disimpan
        unset($data['role_type'], $data['role_name'], $data['name_preview'], $data['permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync permissions setelah role dibuat
        if (isset($this->data['permissions']) && is_array($this->data['permissions'])) {
            $permissions = \Spatie\Permission\Models\Permission::whereIn('name', $this->data['permissions'])->get();
            $this->record->syncPermissions($permissions);
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}