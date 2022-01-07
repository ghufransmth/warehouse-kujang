<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;
    protected $table    = 'tbl_stockopname';

    public function getproduct(){
        return $this->hasOne(Product::class, 'id', 'id_product');
    }
    public function gettransaksi(){
        return $this->hasOne(TransaksiStock::class, 'no_transaksi', 'no_transaksi');
    }
    public function getsatuan(){
        return $this->hasOne(Satuan::class, 'id', 'id_satuan_so');
    }


}
