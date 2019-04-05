<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfferVariantProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_variant_products', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('offer_id')->unsigned();
            $table->integer('variant_id');
            $table->integer('product_id')->default(0);
            $table->string('fake_product_id', 200);
            $table->text('image')->nullable();
            $table->boolean('group')->default(0);
            $table->integer('index')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('offer_id')->references('id')->on('offers')
                    ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offer_variant_products');
    }
}
