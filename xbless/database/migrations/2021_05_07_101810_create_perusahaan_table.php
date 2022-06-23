<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerusahaanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perusahaan', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 200)->nullable();
            $table->string('telephone', 100)->nullable();
            $table->string('bank_name', 150)->nullable();
            $table->string('rek_no', 200)->nullable();
            $table->tinyInteger('flag_perusahaan')->length(1)->default(0)->comment('1: Gudang Aktif; 2: Gudang Lama');
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
        Schema::dropIfExists('perusahaan');
    }
}
