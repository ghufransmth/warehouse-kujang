<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perusahaan extends Model
{
    use HasFactory;
    protected $table = 'perusahaan';

    public function product_beli_details()
    {
        return $this->hasMany(ProductBeliDetail::class, 'perusahaan_id');
    }
}
