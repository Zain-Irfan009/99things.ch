<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->longText('name')->nullable();
            $table->longText('shop_name')->nullable();
            $table->longText('email')->nullable();
            $table->longText('shopify_domain')->nullable();
            $table->longText('shopify_token')->nullable();
            $table->longText('api_key')->nullable();
            $table->longText('api_secret')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('status')->default(1);
            $table->double('price_multiplier')->nullable();
            $table->double('compare_at_price_multiplier')->nullable();
            $table->string('platform')->nullable();
            $table->bigInteger('store_language_id')->nullable();
            $table->bigInteger('autopush_products')->default(0);
            $table->bigInteger('autopush_orders')->default(0);
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
        Schema::dropIfExists('partners');
    }
}
