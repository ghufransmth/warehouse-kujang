<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member_sales', function (Blueprint $table) {
            $table->id();
            $table->integer('member_id')->nullable();
            $table->string('sales_id')->nullable();
            $table->tinyInteger('active')->length(1)->default(0)->comment('0: Tidak Aktif; 1: Aktif');
        });
    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('member_sales');
    }
}
