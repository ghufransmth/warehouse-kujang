<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    use HasFactory;
    protected $table    = 'stock_opname';

    public function getperusahaan()
    {
        return $this->belongsTo('App\Models\Perusahaan','perusahaan_id');
    }

    public function getgudang()
    {
        return $this->belongsTo('App\Models\Gudang','gudang_id');
    }

    public function details()
    {
        return $this->hasMany('App\Models\StockOpnameDetail','so_id');
    }
}
