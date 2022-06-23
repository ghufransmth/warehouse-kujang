<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportStokBmBlTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_stok_bm_bl', function (Blueprint $table) {
            $table->id();
            $table->integer('product_id')->nullable();
            $table->string('transaction_no')->nullable();
            $table->integer('produk_beli_id')->nullable();
            $table->integer('purchase_detail_id')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->integer('perusahaan_id')->nullable();
            $table->integer('gudang_id')->nullable();
            $table->integer('qty_product')->nullable();
            $table->integer('stock_input')->nullable();
            $table->text('note')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('created_by')->nullable();
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
        Schema::dropIfExists('report_stok_bm_bl');
    }
}
