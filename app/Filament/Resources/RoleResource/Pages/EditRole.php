<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Pisahkan name menjadi role_type dan role_name untuk form
        if (isset($data['name'])) {
            $nameParts = explode('.', $data['name'], 2);
            if (count($nameParts) === 2) {
                $data['role_type'] = $nameParts[0];
                $data['role_name'] = $nameParts[1];
                $data['name_preview'] = $data['name'];
            }
        }

        // Ambil permissions untuk form
        $role = $this->getRecord();
        $data['permissions'] = $role->permissions->pluck('name')->toArray();

        Log::info('Form data before fill:', $data);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info('Form data before save:', $data);

        // Gabungkan role_type dan role_name menjadi name
        if (isset($data['role_type']) && isset($data['role_name'])) {
            $data['name'] = $data['role_type'] . '.' . $data['role_name'];
        }

        // Hapus field yang tidak perlu disimpan ke database
        unset($data['role_type'], $data['role_name'], $data['name_preview']);

        // Simpan permissions untuk diproses setelah save
        $permissions = $data['permissions'] ?? [];
        unset($data['permissions']);

        // Simpan permissions di property untuk digunakan di afterSave
        $this->permissions = $permissions;

        Log::info('Processed data for save:', $data);

        return $data;
    }

    protected function afterSave(): void
    {
        $role = $this->getRecord();
        
        Log::info('Role saved:', [
            'id' => $role->id,
            'name' => $role->name,
        ]);

        // Sinkronkan permissions jika ada
        if (isset($this->permissions)) {
            $role->syncPermissions($this->permissions);
            
            Log::info('Permissions synced:', [
                'role_id' => $role->id,
                'permissions' => $this->permissions,
            ]);
        }
    }

    protected $permissions = [];
}