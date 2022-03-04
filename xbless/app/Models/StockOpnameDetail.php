<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOpnameDetail extends Model
{
    use HasFactory;
    protected $table    = 'tbl_stockopname_detail';

    public function getproduct(){
        return $this->hasOne(Product::class, 'id', 'id_product');
    }
}
