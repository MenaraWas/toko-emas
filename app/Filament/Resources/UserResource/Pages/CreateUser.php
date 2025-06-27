<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Jika Admin Branch yang create, assign branch_id otomatis
        if ($user->role->name === 'Admin Branch') {
            $data['branch_id'] = $user->branch_id;
        }

        return $data;
    }
}
