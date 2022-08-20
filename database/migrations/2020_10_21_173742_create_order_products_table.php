<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->increments('order_product_id');
            $table->unsignedInteger('order_id');
            $table->unsignedInteger('product_id');
            $table->bigInteger('product_option_id')->nullable();
            $table->string('size');
            $table->string('color');
            $table->smallInteger('quantity')->default(0);
            $table->smallInteger('return_quantity')->default(0);
            $table->decimal('price')->default(0);
            $table->decimal('total')->default(0);
            $table->timestamps();

            $table->foreign('order_id')->references('order_id')->on('orders');
            $table->foreign('product_id')->references('product_id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_products');
    }
}
