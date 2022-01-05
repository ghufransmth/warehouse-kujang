<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $table    = 'toko';

    public function getcity()
    {
        return $this->belongsTo('App\Models\City', 'city_id');
    }
    public function gettipeharga()
    {
        return $this->belongsTo('App\Models\TipeHarga', 'operation_price');
    }

    public function getpuchaseorder()
    {
        return $this->hasMany(PurchaseOrder::class, 'member_id');
    }
}
