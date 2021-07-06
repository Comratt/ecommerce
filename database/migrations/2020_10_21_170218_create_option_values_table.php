<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('option_values', function (Blueprint $table) {
            $table->increments('option_value_id');
            $table->unsignedInteger('option_id');
            $table->string('image')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->string('name_value', 64);
            $table->string('description', 64)->default('');
            $table->timestamps();

            $table->foreign('option_id')->references('option_id')->on('options');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('option_values');
    }
}
