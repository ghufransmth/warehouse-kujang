<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBeliDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_beli_detail', function (Blueprint $table) {
            $table->id();
            // $table->timestamps();

            $table->integer('produk_beli_id');
            $table->integer('produk_id');
            $table->integer('qty');
            $table->integer('qty_receive');
            $table->integer('perusahaan_id');
            $table->integer('gudang_id');
            $table->datetime('create_date');
            $table->string('create_user',255);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_beli_detail');
    }
}
