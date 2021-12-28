<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBeli extends Model
{
    use HasFactory;

    protected $table   = 'product_beli';
    public $timestamps = false;
    protected $guarded = [];

    public function product_beli_detail()
    {
        return $this->hasOne(ProductBeliDetail::class, 'produk_beli_id');
    }
    public function product_beli_details()
    {
        return $this->hasMany(ProductBeliDetail::class, 'produk_beli_id');
    }
    public function report_stok_bm_bl()
    {
        return $this->hasMany(ReportStock::class, 'produk_beli_id');
    }
}
