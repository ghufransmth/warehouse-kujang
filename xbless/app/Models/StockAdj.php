<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdj extends Model
{
    use HasFactory;
    protected $table    = 'stock_adj';

    public function getperusahaan()
    {
        return $this->belongsTo('App\Models\Perusahaan','perusahaan_id');
    }

    public function getgudang()
    {
        return $this->belongsTo('App\Models\Gudang','gudang_id');
    }

    public function getproduct()
    {
        return $this->belongsTo('App\Models\Product','product_id');
    }

    
}
