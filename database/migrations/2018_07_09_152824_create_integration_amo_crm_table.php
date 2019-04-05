<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationAmoCrmTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_amocrm', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('host', 200);
            $table->string('api_token', 100);
            $table->string('login', 200);
            $table->string('account', 200);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('integration_amocrm');
    }
}
