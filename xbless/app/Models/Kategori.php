<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'tbl_kategori';

    // public function product(){
    //     return $this->belongsToMany(Product::class);
    // }
}
