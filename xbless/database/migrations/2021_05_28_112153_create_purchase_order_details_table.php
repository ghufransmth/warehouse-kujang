<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_purchase_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_purchase_id')
                    ->constrained('transaction_purchase');
            $table->foreignId('gudang_id')
                    ->nullable()
                    ->constrained('gudang');
            $table->foreignId('perusahaan_id')
                    ->nullable()
                    ->constrained('perusahaan');
            $table->foreignId('product_id')
                    ->constrained('product');
            $table->string('product_id_shadow', 100)->nullable();
            $table->integer('qty')->nullable();
            $table->integer('qty_kirim')->nullable();
            $table->double('price')->nullable();
            $table->double('discount')->nullable();
            $table->double('ttl_price')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('colly')->nullable();
            $table->integer('colly_to')->nullable();
            $table->string('satuan')->nullable();
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
        Schema::dropIfExists('transaction_purchase_detail');
    }
}
