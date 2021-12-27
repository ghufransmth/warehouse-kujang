<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductBeliDetail extends Model
{
    use HasFactory;

    protected $table   = 'product_beli_detail';
    public $timestamps = false;
    protected $guarded = [];

    public function product_beli()
    {
        return $this->belongsTo(ProductBeli::class, 'produk_beli_id');
    }

    public function product_belis()
    {
        return $this->belongsTo(ProductBeli::class, 'produk_beli_id');
    }

    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class, 'perusahaan_id');
    }

    public function produk()
    {
        return $this->belongsTo(Product::class, 'produk_id');
    }

    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'gudang_id');
    }
}
