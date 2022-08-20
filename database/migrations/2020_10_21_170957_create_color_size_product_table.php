<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColorSizeProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('color_size_product', function (Blueprint $table) {
            $table->bigIncrements('color_size_product_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('color_id');
            $table->unsignedInteger('size_id');
            $table->integer('quantity')->default(0);
            $table->string('price_prefix', 2)->default('+');
            $table->decimal('price')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('product_id')->on('products');
            $table->foreign('color_id')->references('product_option_id')->on('product_options');
            $table->foreign('size_id')->references('product_option_id')->on('product_options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('color_size_product');
    }
}
