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
        return $this->hasOne(StockOpname::class, 'no_transaksi', 'no_transaksi');
    }
    public function penjualan(){
        return $this->hasOne(Penjualan::class, 'no_faktur', 'no_transaksi')->withDefault([
            'total_harga' => null,
        ]);
    }
    public function pembelian(){
        return $this->hasOne(Pembelian::class, 'no_faktur', 'no_transaksi')->withDefault([
            'nominal' => null,
        ]);
    }
    // public function gettransaksi($id){
    //     $data = self::find($id);
    //     return $data->flag_transaksi;
    //     if($data->flag_transaksi == 3){
    //         return $this->penjualan();
    //     }else{
    //         return $this->pembelian();
    //     }
    // }
}
