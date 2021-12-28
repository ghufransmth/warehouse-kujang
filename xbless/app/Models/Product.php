<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $table = 'product';

    public function perusahaan()
    {
        return $this->belongsToMany(Perusahaan::class);
    }
    public function getsatuan()
    {
        return $this->belongsTo('App\Models\Satuan', 'satuan_id');
    }
    public function getkategori()
    {
        return $this->belongsTo('App\Models\Kategori', 'category_id');
    }
    public function getBrand()
    {
        return $this->belongsTo('App\Models\Brand', 'brand_id');
    }
    public function getEngine()
    {
        return $this->belongsTo('App\Models\Engine', 'engine_id');
    }
    public function satuan()
    {
        return $this->belongsToMany(Satuan::class);
    }

    public function category()
    {
        return $this->belongsToMany(Kategori::class);
    }
    public function getImageDetail()
    {

        return $this->hasMany('App\Models\ProductImg', 'id_product', 'id');
    }
    public function getQrCode()
    {
        return $this->hasMany('App\Models\ProductBarcode', 'product_id', 'id');
    }

    public function produk_beli_details()
    {
        return $this->hasMany(ProductBeliDetail::class, 'produk_id');
    }

    public function satuans()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function invoice_details()
    {
        return $this->hasMany(InvoiceDetail::class, 'product_id');
    }

    public function category_product()
    {
        return $this->belongsTo(Kategori::class, 'category_id');
    }
}
