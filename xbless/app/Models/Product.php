<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    // protected $table = 'product';
    protected $table = 'tbl_product';

    public function getsatuan(){
        return $this->hasOne(Satuan::class, 'id', 'id_satuan');
    }
    public function getkategori(){
        return $this->hasOne(Kategori::class, 'id', 'id_kategori');
    }

}
