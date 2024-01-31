<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->longText('name')->nullable();
            $table->longText('date')->nullable();
            $table->longText('start_time')->nullable();
            $table->longText('end_time')->nullable();
            $table->bigInteger('total_products')->default(0);
            $table->bigInteger('products_pushed')->default(0);
            $table->bigInteger('products_left')->default(0);
            $table->longText('status')->nullable();
            $table->longText('failed_reason')->nullable();
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
        Schema::dropIfExists('logs');
    }
}
