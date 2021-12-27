<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartSales2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_sales_2', function (Blueprint $table) {
            $table->id();
            $table->integer('sales_id')->nullable();
            $table->integer('member_id')->nullable();
            $table->integer('perusahaan_id')->nullable();
            $table->integer('product_id')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_img')->nullable();
            $table->integer('qty')->default(0)->nullable();
            $table->string('satuan')->nullable();
            $table->integer('sub_total')->default(0)->nullable();
            $table->integer('discount')->default(0)->nullable();
            $table->integer('total')->default(0)->nullable();
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
        Schema::dropIfExists('cart_sales_2');
    }
}
