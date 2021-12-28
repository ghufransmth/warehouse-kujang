<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerusahaanGudang extends Model
{
    use HasFactory;
    protected $table = 'perusahaan_gudang';

    public function perusahaan(){
        return $this->belongsToMany(Perusahaan::class);
    }

    public function gudang(){
        return $this->belongsToMany(Gudang::class);
    }
}
