<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use DB;
class GudangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('gudang')->insert([
            [ 'name' => "Jakarta" ],
            [ 'name' => "Semarang"],
            [ 'name' => "Surabaya"],
            [ 'name' => "PIK"],
            [ 'name' => "Cengkareng"],
            [ 'name' => "G1"],
            [ 'name' => "R3"]
        ]);
    }
}
