<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutasi extends Model
{
    use HasFactory;
    protected $table    = 'tbl_mutasi_stock';

    public function satuan(){
        return $this->hasOne(Satuan::class, 'id', 'id_satuan_mutasi');
    }
    public function product(){
        return $this->hasOne(Product::class, 'id', 'id_product');
    }




}
