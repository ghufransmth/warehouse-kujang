<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('toko', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('distrik_id');
            $table->foreign('distrik_id')->references('id')->on('distrik')->onDelete('cascade');
            $table->unsignedBigInteger('tipe_chanel_id');
            $table->foreign('tipe_chanel_id')->references('id')->on('type_channel')->onDelete('cascade');
            $table->unsignedBigInteger('payment_id');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('cascade');
            $table->unsignedBigInteger('jenis_toko_id');
            $table->foreign('jenis_toko_id')->references('id')->on('jenis_toko')->onDelete('cascade');
            $table->unsignedBigInteger('kategori_toko_id');
            $table->foreign('kategori_toko_id')->references('id')->on('kategori_toko')->onDelete('cascade');
            $table->string('code')->unique();
            $table->string('name');
            $table->string('nik');
            $table->string('telp');
            $table->text('alamat');
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
        Schema::dropIfExists('toko');
    }
}
