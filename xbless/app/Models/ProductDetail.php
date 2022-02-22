<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;
    protected $table = 'tbl_product_detail';

    public function getsupplier(){
        return $this->hasOne(Supplier::class, 'id', 'id_supplier');
    }
}
