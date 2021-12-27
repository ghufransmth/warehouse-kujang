<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table    = 'invoice';

    protected $guarded = [];

    public function getMember()
    {
        return $this->belongsTo('App\Models\Member', 'member_id');
    }
    public function getExpedisi()
    {
        return $this->belongsTo('App\Models\Expedisi', 'expedisi');
    }
    public function getPerusahaan()
    {
        return $this->belongsTo('App\Models\Perusahaan', 'perusahaan_id');
    }
    public function getTandaTerima()
    {
        return $this->hasOne('App\Models\InvoiceTandaTerima', 'invoice_id');
    }
    public function getInvoicePiutang()
    {
        return $this->hasMany('App\Models\InvoicePiutang', 'invoice_id');
    }
    public function getInvoicePiutangLast()
    {
        return $this->hasOne('App\Models\InvoicePiutang', 'invoice_id')->orderBy('created_at', 'desc');
    }
    public function invoice_details()
    {
        return $this->hasOne(InvoiceDetail::class, 'invoice_id');
    }

    public function report_bm_bl()
    {
        return $this->hasMany(ReportStock::class, 'invoce_id');
    }

    public function getsales()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    public function getDetail()
    {
        return $this->hasMany('App\Models\InvoiceDetail', 'invoice_id');
    }
    public function getInvoiceReturRevisi()
    {
        return $this->hasMany('App\Models\InvoiceReturRevisi', 'invoice_id')
                    ->whereColumn('qty_before', '>', 'qty_change')
                    ->orWhereColumn('qty_before', '<', 'qty_change')
                    ->whereColumn('price_before', '>', 'price_change')
                    ->orWhereColumn('price_before', '<', 'price_change')
                    ->orderBy('created_at', 'desc');
    }
    public function getReturRevisi()
    {
        return $this->hasMany('App\Models\InvoiceReturRevisi', 'invoice_id');
    }
}
