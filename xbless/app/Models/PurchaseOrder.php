<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table    = 'transaction_purchase';

    public function perusahaan()
    {
        $this->belongsToMany(Perusahaan::class);
    }

    public function member()
    {
        $this->belongsToMany(Member::class);
    }
    public function getmember()
    {
        return $this->belongsTo('App\Models\Member', 'member_id');
    }
    public function getsales()
    {
        return $this->belongsTo('App\Models\Sales', 'sales_id');
    }

    public function sales()
    {
        $this->belongsToMany(Sales::class);
    }

    public function expedisi()
    {
        $this->belongsToMany(Expedisi::class);
    }

    public function expedisi_via()
    {
        $this->belongsToMany(ExpedisiVia::class);
    }

    public function transaction_order()
    {
        return $this->hasMany(ReportStock::class, 'transaction_no', 'no_nota');
    }

    public function purchase_order_detail()
    {
        return $this->hasOne(PurchaseOrderDetail::class, 'transaction_purchase_id');
    }
}
