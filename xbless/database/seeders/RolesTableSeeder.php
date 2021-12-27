<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [ 'name' => "Super Admin" ],
            [ 'name' => "Admin"],
            [ 'name' => "Admin Invoice"],
            [ 'name' => "Admin Gudang"],
            [ 'name' => "Admin PO"],
            [ 'name' => "Admin Tagihan"],
            [ 'name' => "Admin Stok"],
            [ 'name' => "Admin Order"],
        ]);
    }
}
