<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceReturRevisi extends Model
{
    use HasFactory;
    protected $table    = 'invoice_retur_revisi';
    protected $guarded  = [];

    public function getInvoice()
    {
        return $this->belongsTo('App\Models\Invoice', 'invoice_id');
    }

    public function getInvoiceDetail()
    {
        return $this->belongsTo('App\Models\InvoiceDetail', 'invoice_detail_id');
    }
}

