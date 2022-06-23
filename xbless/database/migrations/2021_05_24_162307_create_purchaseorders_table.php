<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_purchase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')
                    ->constrained('perusahaan')->nullable();
            $table->string('no_nota', 100)->nullable();
            $table->string('kode_rpo', 100)->nullable();
            $table->datetime('dataorder')->nullable();
            $table->foreignId('member_id')
                    ->constrained('member');
            $table->string('sales_id', 100)->nullable();
            $table->double('sub_total')->nullable();
            $table->double('discount')->nullable();
            $table->double('total')->nullable();
            $table->string('note')->nullable();
            $table->datetime('duedate')->nullable();
            $table->tinyInteger('status')->length(1)->default(0)->comment('0: NEW; 1: PROSES; 2:REJECTED; 3:SUCCESS');
            $table->tinyInteger('pay_status')->length(1)->default(0)->comment('0: BL; 1: L');
            $table->tinyInteger('access')->length(1)->default(0)->comment('0: BACKORDER; 1: WEB MEMBER; 2: MOBILE APP');
            $table->tinyInteger('status_rpo')->length(1)->default(0)->comment('0: new; 1: ditolak');
            $table->tinyInteger('status_gudang')->length(1)->default(0)->comment('0: Proses_Gudang; 1: Selesai_Gudang; 2: Ditolak Gudang');
            $table->tinyInteger('status_po')->length(1)->default(0)->comment('0: default; 1: done; 2:inputinvoice');
            $table->tinyInteger('flag_status')->length(1)->default(0)->comment('0: po; 1: rpo; 2: bo');
            $table->tinyInteger('read')->length(1)->default(0)->comment('0: Unread; 1: Read');
            $table->string('createdby')->nullable();
            $table->string('createdon')->nullable();
            $table->string('updatedby')->nullable();
            $table->string('updatedon')->nullable();
            $table->integer('count_cetak')->nullable();
            $table->string('updated_po')->nullable();
            $table->string('updated_gudang')->nullable();
            $table->foreignId('expedisi')
                    ->nullable()
                    ->constrained('expedisi');
            $table->foreignId('expedisi_via')
                    ->nullable()
                    ->constrained('expedisi_via');
            $table->datetime('update_date')->nullable();
            $table->datetime('update_date_gudang')->nullable();
            $table->datetime('update_date_invoice')->nullable();
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
        Schema::dropIfExists('transaction_purchase');
    }
}
