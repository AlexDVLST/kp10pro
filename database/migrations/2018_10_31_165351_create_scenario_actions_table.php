<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScenarioActionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scenario_actions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('scenario_id')->unsigned();
            $table->string('action_value');
            $table->string('action_type');

            $table->timestamps();

            $table->foreign('scenario_id')->references('id')->on('scenario')
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
        Schema::dropIfExists('scenario_actions');
    }
}
