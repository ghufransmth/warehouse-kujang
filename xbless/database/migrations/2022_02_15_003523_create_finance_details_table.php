<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finance_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBiginteger('finance_id');
            $table->foreign('finance_id')
                ->references('id')
                ->on('finance')
                ->onDelete('cascade');
            $table->string('name', 175);
            $table->double('nominal');
            $table->text('keterangan');
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
        Schema::dropIfExists('finance_detail');
    }
}
