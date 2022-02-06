<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;
    protected $table = 'pembelian_detail';

    public function getProduct()
    {
       return $this->hasOne(Product::class,'id','product_id');
    }
}
