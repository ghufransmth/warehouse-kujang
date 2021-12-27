<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartSales extends Model
{
    use HasFactory;
    protected $table = 'cart_sales';

    public function member(){
        return $this->hasMany(Member::class);
    }

    public function perusahaan(){
        return $this->hasMany(Perusahaan::class);
    }
    
    public function product(){
        return $this->hasMany(Product::class);
    }

    public function product_img(){
        return $this->hasMany(ProductImg::class);
    }

    public function satuan(){
        return $this->hasMany(Satuan::class);
    }
}
