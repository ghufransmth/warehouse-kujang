<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionSalesFee extends Model
{
    use HasFactory;
    protected $table    = 'transaction_sales_fee';

    protected $guarded = [];

    public function getInvoice()
    {
        return $this->belongsTo('App\Models\Invoice','invoice_id');
    }

}
