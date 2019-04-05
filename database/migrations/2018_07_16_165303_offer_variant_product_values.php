<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OfferVariantProductValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_variant_product_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('variant_product_id')->unsigned();
            $table->text('value');
            $table->integer('index');
            $table->string('type', 100);
            $table->boolean('value_in_price'); //->comment('Usin for custom good column for calculate this value in price');
            $table->timestamps();

            $table->foreign('variant_product_id')->references('id')->on('offer_variant_products')
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
        Schema::dropIfExists('offer_variant_product_values');
    }
}
