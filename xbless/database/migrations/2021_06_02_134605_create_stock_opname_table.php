<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOpnameTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opname', function (Blueprint $table) {
            $table->id();
            $table->string('notransaction')->nullable();
            $table->tinyInteger('flag_proses')->length(1)->default(0)->comment('0:belum diproses; 1:sudah diproses; 2: batal diproses');
            $table->date('faktur_date')->nullable();
            $table->integer('perusahaan_id')->nullable();
            $table->integer('gudang_id')->nullable();
            $table->string('pic')->nullable();
            $table->text('note')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('approved_by')->nullable();
            $table->datetime('approved_at')->nullable();
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
        Schema::dropIfExists('stock_opname');
    }
}
