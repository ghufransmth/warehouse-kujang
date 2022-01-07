<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;
    protected $table    = 'tbl_sales';

    // public function nominal_fee()
    // {
    //     return $this->hasMany('App\Models\TransactionSalesFee', 'sales_id')->sum('fee');
    // }

    // public function sales_invoice()
    // {
    //     return $this->hasMany(Invoice::class, 'sales_id');
    // }
}
