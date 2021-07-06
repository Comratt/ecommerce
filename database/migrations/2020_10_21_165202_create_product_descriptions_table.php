<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_descriptions', function (Blueprint $table) {
            $table->increments('product_description_id');
            $table->unsignedInteger('product_id')->nullable();
            $table->text('description')->default('');
            $table->text('tag')->default('');
            $table->string('meta_title')->default('');
            $table->string('meta_description')->default('');
            $table->string('meta_keyword')->default('');
            $table->timestamps();

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
        Schema::dropIfExists('product_descriptions');
    }
}
