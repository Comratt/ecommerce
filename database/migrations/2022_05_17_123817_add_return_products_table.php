<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReturnProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('return_products', function (Blueprint $table) {
            $table->bigIncrements('return_product_id');
            $table->unsignedInteger('return_id');
            $table->unsignedInteger('products_color_size_id');
            $table->integer('return_quantity');
            $table->timestamps();

            $table->foreign('return_id')->references('return_id')->on('return_products');
            $table->foreign('products_color_size_id')->references('color_size_product_id')->on('color_size_product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('return_products');
    }
}
