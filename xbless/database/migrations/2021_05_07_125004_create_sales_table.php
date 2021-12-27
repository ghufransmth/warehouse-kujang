<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code')->unique();
            $table->string('device')->nullable();
            $table->string('parent_id')->nullable();
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('ktp')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->text('address')->nullable();
            $table->string('no_rek')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('npwp')->nullable();
            $table->string('phone')->nullable();
            $table->enum('jk', ['L', 'P']);
            $table->string('token')->nullable();
            $table->integer('flag_sales')->default(0);
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
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
        Schema::dropIfExists('sales');
    }
}
