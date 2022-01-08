<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukImport extends Model
{
    use HasFactory;
    protected $table = 'tbl_product_import';
    protected $fillable = ['kode_product', 'nama', 'id_kategori', 'id_satuan', 'harga_beli', 'harga_jual'];

    public function getkategori(){
        return $this->hasOne(Kategori::class, 'id', 'id_kategori');
    }
    public function getsatuan(){
        return $this->hasOne(Satuan::class, 'id', 'id_satuan');
    }
}
