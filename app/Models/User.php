<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'branch_id',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*Added for ERP*/
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function permission()
    {
        return $this->belongsToMany(Permission::class, 'user_permission');
    }

    public function hasPermission($permission)
    {
        // Cek permission dari role
        $fromRole = $this->role
            ? $this->role->permissions->contains('name', $permission)
            : false;

        // Cek permission dari user_permission
        $fromUser = $this->permissions->contains('name', $permission);

        return $fromRole || $fromUser;
    }

    public function isSameBranch($branchId)
    {
        return $this->branch_id === $branchId;
    }
}
