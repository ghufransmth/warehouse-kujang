<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductPerusahaanGudangTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_perusahaan_gudang', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->integer('perusahaan_gudang_id')->nullable();
            $table->integer('stok')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_perusahaan_gudang');
    }
}
