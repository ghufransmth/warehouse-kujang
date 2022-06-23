<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                    ->constrained('users');
            $table->foreignId('invoice_id')
                    ->constrained('invoice');
            $table->string('keterangan')->nullable();
            $table->datetime('create_date')->nullable();
            $table->string('create_user', 100)->nullable();
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
        Schema::dropIfExists('invoice_log');
    }
}
