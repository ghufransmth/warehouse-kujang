<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePiutang extends Model
{
    use HasFactory;
    protected $table    = 'invoice_piutang';

    protected $guarded = [];

    public function getPayment()
    {
        return $this->belongsTo('App\Models\Payment', 'payment_id');
    }

    public function getInvoicePayment()
    {
        return $this->belongsTo('App\Models\InvoicePayment', 'invoice_payment_id');
    }
}
