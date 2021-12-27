<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockAdjTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adj', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->integer('perusahaan_id')->nullable();
            $table->integer('gudang_id')->nullable();
            $table->integer('qty_product')->nullable();
            $table->integer('stock_add')->nullable();
            $table->string('note')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_adj');
    }
}
