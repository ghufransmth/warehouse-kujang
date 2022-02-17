<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrder extends Model
{
    use HasFactory;
    protected $table    = 'tbl_delivery_order';

    public function getDriver()
    {
        return $this->belongsTo('App\Models\Driver', 'driver_id');
    }
    // <!-- DO202202150000000005 -->
}
