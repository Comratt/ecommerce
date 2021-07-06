<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_options', function (Blueprint $table) {
            $table->increments('product_option_id');
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('option_id');
            $table->unsignedInteger('option_value_id');
            $table->integer('quantity')->default(0);
            $table->string('price_prefix', 2)->default('+');
            $table->decimal('price')->default(0);
            $table->timestamps();

            $table->foreign('product_id')->references('product_id')->on('products');
            $table->foreign('option_id')->references('option_id')->on('options');
            $table->foreign('option_value_id')->references('option_value_id')->on('option_values');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_options');
    }
}
