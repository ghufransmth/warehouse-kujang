<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;
    protected $table    = 'master_provinsi';
    public $timestamps = false; 

    public function getnegara()
    {
        return $this->belongsTo('App\Models\Country','country_id');
    }
}
