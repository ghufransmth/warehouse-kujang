<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('payments')->insert([
            [ 'name' => "Giro" ],
            [ 'name' => "Transfer"],
            [ 'name' => "Cash"],
            [ 'name' => "Cek"],
            [ 'name' => "Retur"],
            [ 'name' => "Revisi"],
            [ 'name' => "-"]
        ]);
    }
}
