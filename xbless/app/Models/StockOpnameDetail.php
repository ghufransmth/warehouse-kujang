<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use HasFactory;
    protected $table    = 'stock_opname_detail';

    public function product()
    {
        return $this->belongsTo('App\Models\Product','produk_id');
    }
}
