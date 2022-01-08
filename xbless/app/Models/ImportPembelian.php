<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportPembelian extends Model
{
    use HasFactory;
    protected $table = 'tbl_detail_pembelian_import';

    protected $fillable = ['no_faktur', 'kode_product', 'satuan_id', 'qty', 'harga_product', 'total_harga'];
}
