<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
use App\Models\User;
use App\Models\Role;
class RoleuserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    
    public function run()
    {
        $users = User::pluck('id')->toArray();
        $role_manager  = Role::where('name', 'Super Admin')->first();
        $role_manager->user()->attach($users); 
    }
}
