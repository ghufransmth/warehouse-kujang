<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_paper_number')->nullable();
            $table->string('no_nota')->nullable();
            $table->string('purchase_no')->nullable();
            $table->datetime('dateorder')->nullable();
            $table->datetime('dateprint')->nullable();
            $table->integer('member_id')->nullable();
            $table->integer('sales_id')->nullable();
            $table->integer('perusahaan_id')->nullable();
            $table->double('subtotal')->nullable();
            $table->double('discount')->nullable();
            $table->double('total')->nullable();
            $table->double('total_before_ppn')->nullable();
            $table->double('total_before_diskon')->nullable();
            $table->string('note')->nullable();
            $table->datetime('duedate')->nullable();
            $table->date('min_duedate')->nullable();
            $table->tinyInteger('flag_duedate')->length(1)->default(0)->comment('0:Aman; 1:Warning');
            $table->tinyInteger('flag_tanda_terima')->length(1)->default(0)->comment('0:belum dibuat; 1:sudah dibuat');
            $table->tinyInteger('pay_status')->length(1)->default(0)->comment('0:BL; 1:L');
            $table->tinyInteger('access')->length(1)->default(0)->comment('0:BACKORDER; 1:WEB MEMBER; 2:MOBILE APP');
            $table->string('expedisi')->nullable();
            $table->string('via_expedisi')->nullable();
            $table->datetime('delivery_date')->nullable();
            $table->string('resi_no')->nullable();
            $table->tinyInteger('read')->length(1)->default(0)->comment('0:Unread; 1:Read');
            $table->integer('count_print')->nullable();
            $table->tinyInteger('flag_giro_cek')->default(0);
            $table->datetime('invoice_date_tt')->nullable();
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
        Schema::dropIfExists('invoice');
    }
}
