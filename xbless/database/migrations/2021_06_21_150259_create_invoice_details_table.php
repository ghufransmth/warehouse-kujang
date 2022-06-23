<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                    ->constrained('invoice');
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_img')->nullable();
            $table->integer('qty')->nullable();
            $table->integer('qty_kirim')->nullable();
            $table->string('satuan')->nullable();
            $table->string('deskripsi')->nullable();
            $table->double('discount')->nullable();
            $table->double('price')->nullable();
            $table->double('ttl_price')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('colly')->nullable();
            $table->integer('colly_to')->nullable();
            $table->integer('gudang_id')->nullable();
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
        Schema::dropIfExists('invoice_detail');
    }
}
