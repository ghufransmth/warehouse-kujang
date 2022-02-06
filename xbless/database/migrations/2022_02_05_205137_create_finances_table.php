<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('komponen_biaya_id');
            $table->foreign('komponen_biaya_id')
                ->references('id')
                ->on('komponen_biaya')
                ->onDelete('cascade');
            $table->string('name', 150);
            $table->tinyInteger('kategpri');
            $table->double('total');
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
        Schema::dropIfExists('finance');
    }
}
