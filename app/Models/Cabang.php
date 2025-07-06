<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

     protected $fillable = [
        'nama',
        'kode',
        'alamat',
        'telepon',
    ];

    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
