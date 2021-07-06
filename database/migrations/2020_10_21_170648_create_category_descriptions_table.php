<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoryDescriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_descriptions', function (Blueprint $table) {
            $table->increments('category_description_id');
            $table->unsignedInteger('category_id');
            $table->text('description')->default('');
            $table->text('tag')->default('');
            $table->string('meta_title')->default('');
            $table->string('meta_description')->default('');
            $table->string('meta_keywords')->default('');
            $table->timestamps();

            $table->foreign('category_id')->references('category_id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('category_descriptions');
    }
}
