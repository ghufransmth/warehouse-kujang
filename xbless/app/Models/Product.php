<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    // protected $table = 'product';
    protected $table = 'tbl_product';

    public function getsatuan(){
        return $this->hasOne(Satuan::class, 'id', 'id_satuan');
    }
    public function getkategori(){
        return $this->hasOne(Kategori::class, 'id', 'id_kategori');
    }
    public function getstock(){
        return $this->hasOne(StockAdj::class, 'id_product', 'id')->ofMany('gudang_baik','max');
    }
    public function getdetailproduct(){
        return $this->hasMany(ProductDetail::class, 'id_product', 'id');
    }

}
