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
    public function getdetail(){
        return $this->hasMany(StockMutasiDetail::class, 'id_mutasi', 'id');
    }
    public function getgudangawal(){
        return $this->hasOne(Gudang::class, 'id', 'gudang_dari');
    }
    public function getgudangtujuan(){
        return $this->hasOne(Gudang::class, 'id', 'gudang_tujuan');
    }




}
