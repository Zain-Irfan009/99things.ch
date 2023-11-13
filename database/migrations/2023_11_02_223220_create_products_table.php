<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('partner_shopify_id')->nullable();
            $table->bigInteger('partner_id')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('handle')->nullable();
            $table->string('vendor')->nullable();
            $table->string('type')->nullable();
            $table->text('featured_image')->nullable();
            $table->longText('tags')->nullable();
            $table->longText('options')->nullable();
            $table->text('images')->nullable();
            $table->text('status')->nullable();
            $table->string('published_at')->nullable();
            $table->date('approve_date')->nullable();
            $table->bigInteger('shopify_id')->nullable();
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
        Schema::dropIfExists('products');
    }
}
