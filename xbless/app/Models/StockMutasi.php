<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMutasi extends Model
{
    use HasFactory;
    protected $table    = 'log_stock';

    public function getperusahaanFrom()
    {
        return $this->belongsTo('App\Models\Perusahaan','from_perusahaan_id');
    }
    public function getperusahaanTo()
    {
        return $this->belongsTo('App\Models\Perusahaan','to_perusahaan_id');
    }

    public function getgudangAwal()
    {
        return $this->belongsTo('App\Models\Gudang','from_gudang_id');
    }
    public function getgudangTujuan()
    {
        return $this->belongsTo('App\Models\Gudang','to_gudang_id');
    }

    public function getproduct()
    {
        return $this->belongsTo('App\Models\Product','product_id');
    }

    
}
