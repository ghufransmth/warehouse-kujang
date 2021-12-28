<?php

namespace App\Models;

use App\Models\Invoice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceTandaTerima extends Model
{
    use HasFactory;
    protected $table    = 'invoice_tanda_terima';
    protected $guarded = [];


    public function getMember()
    {
        return $this->belongsTo('App\Models\Member', 'member_id');
    }

    public function getPerusahaan()
    {
        return $this->belongsTo('App\Models\Perusahaan', 'perusahaan_id');
    }

    public function getInvoice()
    {
        return $this->belongsTo('App\Models\Invoice', 'invoice_id');
    }

    public function getExpedisi()
    {
        return $this->belongsTo('App\Models\Expedisi', 'expedisi');
    }

    protected function setPrimaryKey($key)
    {
        $this->primaryKey = $key;
    }

    public function invoiceNoNota()
    {
        // $this->setPrimaryKey('member_id');

        // $relation = $this->hasMany('App\Models\Invoice', 'member_id')->where('flag_tanda_terima', 1);

        // $this->setPrimaryKey('id');

        // return $relation;
        return $this->hasMany(Invoice::class, 'member_id', 'member_id')->where('flag_tanda_terima', 1);
    }

    public function invoicePayment()
    {
        return $this->hasMany(InvoicePayment::class, 'no_tanda_terima', 'no_tanda_terima')->where('cicilan_ke', '!=', 0);
    }
    public function invoicePiutang()
    {
        return $this->belongsTo(InvoicePiutang::class, 'no_tanda_terima', 'no_tt');
    }

    public function getinvoicepayment()
    {
        // return $this->hasMany(InvoicePayment::class, 'no_tanda_terima', 'no_tanda_terima')->where('sisa', '>', 6000);
        return $this->hasMany(InvoicePayment::class, 'no_tanda_terima', 'no_tanda_terima');
    }

    public function getinvoicePaymentDesc()
    {
        return $this->hasOne(InvoicePayment::class, 'no_tanda_terima', 'no_tanda_terima')->orderBy('id', 'desc');
    }
}
