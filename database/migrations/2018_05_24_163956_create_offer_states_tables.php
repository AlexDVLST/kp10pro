<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfferStatesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offer_states', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('state_id');
            $table->integer('offer_id')->unsigned();
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
        Schema::dropIfExists('offer_states');
    }
}
