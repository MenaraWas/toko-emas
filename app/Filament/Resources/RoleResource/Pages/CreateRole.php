<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Form data before create:', $data);

        // Gabungkan role_type dan role_name menjadi name
        if (isset($data['role_type']) && isset($data['role_name'])) {
            $data['name'] = $data['role_type'] . '.' . $data['role_name'];
        }

        // Hapus field yang tidak perlu disimpan ke database
        unset($data['role_type'], $data['role_name'], $data['name_preview']);

        // Simpan permissions untuk diproses setelah create
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        // Simpan permissions di property untuk digunakan di afterCreate
        $this->permissions = $permissions;

        Log::info('Processed data for create:', $data);

        return $data;
    }

    protected function afterCreate(): void
    {
        $role = $this->getRecord();
        
        Log::info('Role created:', [
            'id' => $role->id,
            'name' => $role->name,
        ]);

        // Sinkronkan permissions jika ada
        if (isset($this->permissions) && !empty($this->permissions)) {
            $role->syncPermissions($this->permissions);
            
            Log::info('Permissions synced:', [
                'role_id' => $role->id,
                'permissions' => $this->permissions,
            ]);
        }
    }

    protected $permissions = [];
}