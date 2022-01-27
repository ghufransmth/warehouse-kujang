<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKunjunganSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kunjungan_sales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sales_id');
            $table->unsignedBigInteger('hari_id');
            $table->foreign('hari_id')
                ->references('id')
                ->on('hari')
                ->onDelete('cascade');
            $table->tinyInteger('skala')->lenght(1)->comment('0: weekly; 1: biweekly;');
            $table->unsignedBigInteger('toko_id');
            $table->foreign('toko_id')
                ->references('id')
                ->on('toko')
                ->onDelete('cascade');
            $table->string('faktur_piutang', 150)->nullable();
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
        Schema::dropIfExists('kunjungan_sales');
    }
}
