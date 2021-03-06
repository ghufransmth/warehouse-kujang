<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailReturTransaksi extends Model
{
    use HasFactory;
    protected $table = 'detail_retur_transaksi';

    public function getproduct(){
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
