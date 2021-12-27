<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBarcodesTable extends Migration
{
    /**
     * Run the migrations.
     *
    * @return void
     */
    public function up()
    {
        Schema::create('product_barcode', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                    ->constrained('product');
            $table->string('barcode')->nullable();
            $table->integer('isi')->nullable();
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
        Schema::dropIfExists('product_barcode');
    }
}
