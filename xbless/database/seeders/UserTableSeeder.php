<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'fullname'      => 'Vexia Alexandra',
                'ktp'           => '-',
                'email'         => 'vexia@gmail.com',
                'password'      => bcrypt('blessing'),
                'jk'            => 'P',
                'username'      => 'vexia',
                'status'        => 1,
                'flag_user'     => 1,
                'created_by'    => 'System',
                'created_at'    => now(),
               
            ]
        ]);
    }
}
