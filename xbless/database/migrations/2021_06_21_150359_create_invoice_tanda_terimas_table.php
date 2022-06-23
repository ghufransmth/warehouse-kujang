<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTandaTerimasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_tanda_terima', function (Blueprint $table) {
            $table->id();
            $table->string('no_tanda_terima')->nullable();
            $table->integer('invoice_id')->nullable();
            $table->string('member_id')->nullable();
            $table->string('perusahaan_id')->nullable();
            $table->string('resi_no')->nullable();
            $table->string('expedisi')->nullable();
            $table->string('delivery_date')->nullable();
            $table->double('nilai')->nullable();
            $table->integer('flag_giro_cek')->default(0);
            $table->datetime('invoice_date')->nullable();
            $table->datetime('create_date')->nullable();
            $table->datetime('create_user')->nullable();
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
        Schema::dropIfExists('invoice_tanda_terima');
    }
}
