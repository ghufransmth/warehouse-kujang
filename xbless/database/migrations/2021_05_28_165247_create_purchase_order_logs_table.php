<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_purchase_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')
                    ->constrained('transaction_purchase');
            $table->foreignId('user_id')
                    ->constrained('users');
            $table->string('keterangan');
            $table->datetime('create_date');
            $table->string('create_user');
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
        Schema::dropIfExists('transaction_purchase_log');
    }
}
