<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePiutangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_piutang', function (Blueprint $table) {
            $table->id();
            $table->integer('invoice_id')->nullable();
            $table->string('no_tt')->nullable();
            $table->integer('invoice_payment_id')->nullable();
            $table->integer('payment_id')->nullable();
            $table->double('total')->nullable();
            $table->double('sisa')->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('flag')->nullable();
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
        Schema::dropIfExists('invoice_piutang');
    }
}
