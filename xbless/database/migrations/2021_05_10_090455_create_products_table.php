<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->id();
            $table->string('product_code')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_cover')->nullable()->default('web/images/no_img.png');
            $table->string('product_desc')->nullable();
            $table->foreignId('category_id')
                    ->nullable()
                    ->constrained('category_product');
            $table->integer('brand_id')->nullable();
            $table->integer('engine_id')->nullable();
            $table->integer('stock_min')->nullable();
            $table->integer('stock')->nullable();
            $table->double('normal_price')->nullable();
            $table->double('export_price')->nullable();
            $table->string('product_status')->default(1);
            $table->foreignId('perusahaan_id')
                    ->nullable()
                    ->constrained('perusahaan');
            $table->foreignId('satuan_id')
                    ->nullable()
                    ->constrained('satuan');
            $table->integer('satuan_value')->nullable();
            $table->string('engine_model')->nullable();
            $table->string('fact_no')->nullable();
            $table->string('oem_no')->nullable();
            $table->string('part_no')->nullable();
            $table->string('supplier')->nullable();
            $table->string('barcode')->nullable();
            $table->string('product_code_shadow')->nullable();
            $table->enum('is_liner', ['Y', 'N']);
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
        Schema::dropIfExists('product');
    }
}
