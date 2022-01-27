<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;
    protected $table = 'pembelian';

    public function getDetailPembelian()
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_id','id')->with('getProduct');
    }
}
