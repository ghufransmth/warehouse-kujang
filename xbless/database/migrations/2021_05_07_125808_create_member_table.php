<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('member', function (Blueprint $table) {
            $table->id();
            $table->string('uniq_code')->unique();
            $table->string('device')->nullable();
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('ktp')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->text('address')->nullable();
            $table->text('address_toko')->nullable();
            $table->integer('city_id')->nullable();
            $table->string('city')->nullable();
            $table->string('prov')->nullable();
            $table->string('no_rek')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('holder_name')->nullable();
            $table->string('npwp')->nullable();
            $table->string('phone')->nullable();
            $table->integer('operation_price')->nullable();
            $table->string('token')->nullable();
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
        Schema::dropIfExists('member');
    }
}
