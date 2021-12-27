<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBarcode extends Model
{
    use HasFactory;
    protected $table = 'product_barcode';

    public function product()
    {
        return $this->belongsToMany(Product::class);
    }
    public function getProduct()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
