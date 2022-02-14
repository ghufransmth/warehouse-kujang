<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturTransaksi extends Model
{
    use HasFactory;
    protected $table = 'retur_transaksi';

    public function penjualan(){

    }

    public function pembelian(){

    }
    public function getsales(){
        return $this->hasOne(Sales::class, 'id', 'id_sales');
    }
    public function gettoko(){
        return $this->hasOne(Toko::class, 'id', 'id_toko');
    }
}
