<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;
    protected $table = 'pembelian';

    public function getdetailpembelian(){
        return $this->hasMany(PembelianDetail::class, 'pembelian_id', 'id');
    }

    public function gettransaksi(){
        return $this->hasOne(TransaksiStock::class, 'no_transaksi', 'no_faktur');
    }
    public function getsupplier(){
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }

    public function getgudang(){
        return $this->hasOne(Gudang::class, 'id', 'id_gudang');
    }
}
