<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderLog extends Model
{
    use HasFactory;
    protected $table = 'transaction_purchase_log';

    public function purchase(){
        $this->belongsToMany(PurchaseOrder::class);
    }
    public function user(){
        $this->belongsToMany(User::class);
    }
}
