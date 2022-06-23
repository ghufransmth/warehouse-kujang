<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_product', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->nullable();
            $table->string('cat_code', 200)->nullable();
            $table->string('cat_name', 200)->nullable();
            $table->string('cat_sub_name', 200)->nullable();
            $table->string('cat_image', 200)->nullable();
            $table->tinyInteger('cat_status')->length(1)->default(0)->comment('0: Tidak Aktif; 1: Aktif;');
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
        Schema::dropIfExists('category_product');
    }
}
