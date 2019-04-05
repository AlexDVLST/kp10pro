<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientPositionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_positions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('position', 255)->nullable();
            $table->integer('client_id')->unsigned();
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')
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
        Schema::dropIfExists('client_positions');
    }
}
