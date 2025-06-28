<?php

namespace App\Observers;

use App\Models\Role;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     */
    public function created(Role $role): void
    {
        //
        // Hanya berlaku untuk role bertipe 'branch'
        if ($role->type === 'branch') {
            // Cari semua role Admin Branch
            $adminBranchRoles = \App\Models\Role::where('name', 'Admin Branch')->pluck('id');

            foreach ($adminBranchRoles as $adminRoleId) {
                DB::table('role_assignable')->insert([
                    'role_id' => $adminRoleId,
                    'assignable_role_id' => $role->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Handle the Role "updated" event.
     */
    public function updated(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "deleted" event.
     */
    public function deleted(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "restored" event.
     */
    public function restored(Role $role): void
    {
        //
    }

    /**
     * Handle the Role "force deleted" event.
     */
    public function forceDeleted(Role $role): void
    {
        //
    }
}
