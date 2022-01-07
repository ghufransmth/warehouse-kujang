<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    use HasFactory;
    protected $table = "tbl_detail_penjualan";

    public function getproduct(){
        return $this->hasOne(Product::class, 'id', 'id_product');
    }
}
