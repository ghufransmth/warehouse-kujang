<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportStock extends Model
{
    use HasFactory;
    protected $table    = 'report_stok_bm_bl';

    public function getperusahaan()
    {
        return $this->belongsTo('App\Models\Perusahaan', 'perusahaan_id');
    }

    public function getgudang()
    {
        return $this->belongsTo('App\Models\Gudang', 'gudang_id');
    }

    public function getproduct()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }

    public function getinvoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function produk_beli()
    {
        return $this->belongsTo(ProductBeli::class, 'produk_beli_id');
    }

    public function transaction_detail()
    {
        return $this->belongsTo(PurchaseOrderDetail::class, 'purchase_detail_id');
    }

    public function transaction_order_bm_bl()
    {
        return $this->belongsTo(PurchaseOrder::class, 'transaction_no', 'no_nota')->where('flag_status', 0);
    }
}
