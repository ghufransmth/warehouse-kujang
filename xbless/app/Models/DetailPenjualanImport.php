<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenjualanImport extends Model
{
    use HasFactory;
    protected $table = 'tbl_detail_penjualan_import';

    protected $fillable = ['id_sales','id_toko','no_faktur', 'kode_product', 'id_satuan', 'qty', 'harga_product', 'total_harga'];
}
