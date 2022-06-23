<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePerusahaanGudangsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('perusahaan_gudang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')
                    ->constrained('perusahaan');
            $table->foreignId('gudang_id')
                    ->constrained('gudang');
            $table->enum('active', [0, 1])->default(1);
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
        Schema::dropIfExists('perusahaan_gudang');
    }
}
