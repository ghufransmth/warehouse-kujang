<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;
    protected $table = "tbl_penjualan";

    public function getsales(){
        return $this->hasOne(Sales::class, 'id', 'id_sales');
    }
    public function gettoko(){
        return $this->hasOne(Toko::class, 'id', 'id_toko');
    }
    public function gettransaksi(){
        return $this->hasOne(TransaksiStock::class, 'no_transaksi', 'no_faktur');
    }
    public function getdetailpenjualan(){
        return $this->hasMany(DetailPenjualan::class, 'no_faktur', 'no_faktur');
    }



}
