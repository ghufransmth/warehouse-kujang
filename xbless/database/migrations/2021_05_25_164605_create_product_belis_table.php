<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBelisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_beli', function (Blueprint $table) {
            $table->id();
            $table->string('notransaction', 255);
            $table->integer('status');
            $table->string('factory_name',255);
            $table->integer('flag_proses');
            $table->datetime('faktur_date');
            $table->datetime('warehouse_date');
            $table->string('note',255);
            $table->datetime('create_date');
            $table->string('create_user',255);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_beli');
    }
}
