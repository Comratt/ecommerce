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
            $table->string('email', 100)->nullable();
            $table->string('phone', 32);
            $table->string('shipping_country', 32);
            $table->string('shipping_city', 32);
            $table->string('shipping_area', 32);
            $table->string('shipping_address', 128);
            $table->integer('promocode_id')->nullable();
            $table->decimal('promocode_discount')->default(0);
            $table->text('comment')->default('');
            $table->boolean('viewed')->default(0);
            $table->unsignedBigInteger('manager_id')->nullable();
            // 1 - throw NovaPoshta, 2 - online pay success, 3 - online pay failure
            $table->integer('payment_status')->default(1);
            $table->timestamps();

            $table->foreign('status_id')->references('order_status_id')->on('order_statuses');
            $table->foreign('manager_id')->references('id')->on('users');
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
