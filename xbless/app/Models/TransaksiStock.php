<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransaksiStock extends Model
{
    use HasFactory;
    protected $table    = 'tbl_transaksi_stock';

    public function detail_mutasi(){
        return $this->hasMany(StockMutasi::class, 'no_transaksi', 'no_transaksi');
    }
    public function detail_stockopname(){
        return $this->hasMany(StockOpname::class, 'no_transaksi', 'no_transaksi');
    }
}
