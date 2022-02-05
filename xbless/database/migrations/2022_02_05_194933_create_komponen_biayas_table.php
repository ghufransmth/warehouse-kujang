<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKomponenBiayasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('komponen_biaya', function (Blueprint $table) {
            $table->id();
            $table->string('code', 75);
            $table->string('name', 150);
            $table->tinyInteger('kategori')->length(1)->comment('0: Debit; 1: Kredit;');
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
        Schema::dropIfExists('komponen_biaya');
    }
}
