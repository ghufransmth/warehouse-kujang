<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberSales extends Model
{
    use HasFactory;
    protected $table    = 'member_sales';
    public $timestamps = false; 
}
