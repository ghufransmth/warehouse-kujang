<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogDecrementStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_decrement_stock', function (Blueprint $table) {
            $table->id();
            $table->string('no_nota')->nullable();
            $table->integer('id_stock')->nullable();
            $table->integer('decrement')->nullable();
            $table->integer('node')->nullable();
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
        Schema::dropIfExists('log_decrement_stock');
    }
}
