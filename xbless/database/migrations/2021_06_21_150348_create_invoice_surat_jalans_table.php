<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceSuratJalansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_surat_jalan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')
                    ->constrained('invoice');
            $table->string('surat_jalan_no')->nullable();
            $table->string('create_user')->nullable();
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
        Schema::dropIfExists('invoice_surat_jalan');
    }
}
