<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $table    = 'city';
    public $timestamps = false;  
    
    public function getprovinsi()
    {
        return $this->belongsTo('App\Models\Provinsi','provinsi_id');
    }
}
