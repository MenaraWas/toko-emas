<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode',
        'supplier_name',
        'tanggal_order',
        'status',
        'catatan',
        'created_by',
    ];

     public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
