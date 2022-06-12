<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('order_id');
            $table->unsignedInteger('status_id');
            $table->string('first_name', 64);
            $table->string('last_name', 64);
            $table->string('phone', 32);
            $table->string('shipping_country', 32);
            $table->string('shipping_city', 32);
            $table->string('shipping_address', 128);
            $table->text('comment')->default('');
            $table->boolean('viewed')->default(0);
            $table->timestamps();

            $table->foreign('status_id')->references('order_status_id')->on('order_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
