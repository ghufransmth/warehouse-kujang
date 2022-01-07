<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiskonDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('diskon_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('diskon_id');
            $table->foreign('diskon_id')
                ->references('id')
                ->on('diskon')
                ->onDelete('cascade');
            $table->double('min_beli')->nullable();
            $table->double('max_beli')->nullable();
            $table->double('nilai_diskon')->nullable();
            $table->tinyInteger('jenis_diskon')->lenght(1)->nullable()->comment('0: Diskon Uang; 1: Bonus Barang');
            $table->tinyInteger('kelipatan')->lenght(1)->default(1)->comment('0: Y; 1: T');
            $table->string('produk')->nullable();
            $table->integer('jml_produk')->nullable();
            $table->string('bonus_produk')->nullable();
            $table->integer('jml_bonus')->nullable();
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
        Schema::dropIfExists('diskon_detail');
    }
}
