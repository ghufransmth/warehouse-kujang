<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderDetail extends Model
{
    use HasFactory;
    protected $table = 'transaction_purchase_detail';

    public function gudang()
    {
        $this->belongsToMany(Gudang::class);
    }

    public function product()
    {
        $this->belongsToMany(Product::class);
    }

    public function purchase()
    {
        $this->belongsToMany(PurchaseOrder::class);
    }

    public function report_stok_bm_bl()
    {
        return $this->hasOne(ReportStock::class, 'purchase_detail_id');
    }

    public function purchase_order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'transaction_purchase_id');
    }
}
