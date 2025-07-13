<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'kode',
        'nama',
        'karat',
        'berat',
        'harga_dasar',
        'aktif',
    ];

    protected static function booted()
    {
        static::creating(function ($product) {
            // Cek apakah kode sudah terisi dari form
            if (empty($product->kode)) {
                $prefix = 'EMAS-' . now()->format('Ym');

                // Cari jumlah produk bulan ini
                $count = self::where('kode', 'like', $prefix . '%')->count() + 1;

                // Format ke EMAS-202507-001
                $product->kode = $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
            }
        });
    }

    public function registerMediaConversions(\Spatie\MediaLibrary\MediaCollections\Models\Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);
    }
}