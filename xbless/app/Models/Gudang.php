<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    use HasFactory;
    protected $table = 'gudang';

    public function product_beli_details()
    {
        return $this->hasMany(Gudang::class, 'gudang_id');
    }

    public function perusahaan_gudang()
    {
        return $this->hasOne(PerusahaanGudang::class, 'gudang_id');
    }
}
