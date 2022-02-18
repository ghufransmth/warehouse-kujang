<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory;
    protected $table    = 'tbl_sales';
    protected $fillable = ['user_id', 'code', 'nama'];


}
