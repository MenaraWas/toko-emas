<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Parse nama role yang ada untuk ditampilkan di form
        if (isset($data['name'])) {
            $nameParts = explode('.', $data['name'], 2);
            if (count($nameParts) === 2) {
                $data['role_type'] = $nameParts[0];
                $data['role_name'] = $nameParts[1];
                $data['name_preview'] = $data['name'];
            }
        }

        // Load existing permissions
        $data['permissions'] = $this->record->permissions->pluck('name')->toArray();

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Untuk edit, kita tidak mengubah nama role
        // Hapus field yang tidak perlu
        unset($data['role_type'], $data['role_name'], $data['name_preview']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync permissions setelah role diupdate
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