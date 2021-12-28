<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    protected $table = 'roles';
    public $timestamps = false;   

    public function user()
    {
        return $this->belongsToMany('App\Models\User','roleuser');
    }
    public function permission()
    {
        return $this->belongsToMany('App\Models\Permission','permissionrole');
    }

}