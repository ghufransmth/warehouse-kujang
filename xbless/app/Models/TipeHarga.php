<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipeHarga extends Model
{
    use HasFactory;
    protected $table    = 'type_price';
    public $timestamps = false;
}
