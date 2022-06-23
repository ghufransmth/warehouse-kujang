<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_stock', function (Blueprint $table) {
            $table->id();
            $table->integer('product_perusahaan_gudang_id')->nullable();
            $table->string('no_transaction')->nullable();
            $table->integer('from_perusahaan_id')->nullable();
            $table->integer('from_gudang_id')->nullable();
            $table->integer('to_perusahaan_id')->nullable();
            $table->integer('to_gudang_id')->nullable();
            $table->integer('from_stock')->nullable();
            $table->integer('to_stock')->nullable();
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
        Schema::dropIfExists('log_stock');
    }
}
