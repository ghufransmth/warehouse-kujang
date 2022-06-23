<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment', function (Blueprint $table) {
            $table->id();
            $table->integer('member_id')->nullable();
            $table->integer('payment_id')->nullable();
            $table->string('no_tanda_terima')->nullable();
            $table->datetime('payment_date')->nullable();
            $table->datetime('liquid_date')->nullable();
            $table->string('name')->nullable();
            $table->string('number', 100)->nullable();
            $table->double('total_pembayaran')->nullable();
            $table->double('sisa')->nullable();
            $table->double('sudah_dibayar')->nullable();
            $table->string('keterangan')->nullable();
            $table->integer('cicilan_ke')->nullable();
            $table->tinyInteger('flag')->length(1)->default(0)->comment('0:BL; 1:L');
            $table->tinyInteger('flag_giro_cek')->default(0);
            $table->datetime('filter_date')->nullable();
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
        Schema::dropIfExists('invoice_payment');
    }
}
