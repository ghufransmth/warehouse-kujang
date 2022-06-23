<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceReturRevisisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_retur_revisi', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_retur_revisi')->nullable();
            $table->integer('invoice_tanda_terima')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->integer('invoice_detail_id')->nullable();
            $table->string('note')->nullable();
            $table->integer('qty_before')->nullable();
            $table->integer('qty_change')->nullable();
            $table->integer('price_before')->nullable();
            $table->integer('price_change')->nullable();
            $table->integer('total_before')->nullable();
            $table->integer('total_change')->nullable();
            $table->string('create_user')->nullable();
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
        Schema::dropIfExists('invoice_retur_revisi');
    }
}
