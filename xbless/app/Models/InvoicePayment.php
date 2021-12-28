<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory;
    protected $table    = 'invoice_payment';

    protected $guarded = [];

    public function getPayment()
    {
        return $this->belongsTo('App\Models\Payment', 'payment_id');
    }

    public function getMember()
    {
        return $this->belongsTo('App\Models\Member', 'member_id');
    }

    public function getinvocetandaterima()
    {
        return $this->belongsTo(InvoceTandaTerima::class, 'no_tanda_terima', 'no_tanda_terima');
    }
}
