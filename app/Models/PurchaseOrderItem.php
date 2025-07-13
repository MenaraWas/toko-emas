<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

     protected $fillable = [
        'purchase_order_id',
        'product_id',
        'kuantitas',
        'harga_satuan',
        'total_harga',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
