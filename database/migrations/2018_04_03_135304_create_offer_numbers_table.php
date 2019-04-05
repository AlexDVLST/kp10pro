<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferNumbersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_numbers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('number');
            $table->integer('offer_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('offer_numbers', function (Blueprint $table) {
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
        Schema::dropIfExists('offer_numbers');
    }
}
