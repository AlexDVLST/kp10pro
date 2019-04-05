<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemScenarioAdditionalEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_scenario_additional_events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('event_id');
            $table->string('additional_event_name');
            $table->string('additional_event_value');
            $table->string('type');
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
        Schema::dropIfExists('system_scenario_additional_events');
    }
}
