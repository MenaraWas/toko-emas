<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load existing roles untuk ditampilkan di form
        $data['roles'] = $this->record->roles->pluck('id')->toArray();
        
        return $data;
    }

    protected function afterSave(): void
    {
        // Sync roles setelah user diupdate
        if (isset($this->data['roles']) && is_array($this->data['roles'])) {
            $roles = \Spatie\Permission\Models\Role::whereIn('id', $this->data['roles'])->get();
            $this->record->syncRoles($roles);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Jika bukan owner, pastikan cabang_id tidak berubah
        if (!auth()->user()->hasRole('owner')) {
            $data['cabang_id'] = $this->record->cabang_id;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}