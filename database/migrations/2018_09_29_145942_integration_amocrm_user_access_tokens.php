<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class IntegrationAmocrmUserAccessTokens extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_amocrm_user_access_tokens', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();

            $table->integer('amocrm_user_id');
            $table->text('access_token');
            $table->integer('account_id')->unsigned();

            $table->foreign('account_id')->references('id')->on('users')
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
        Schema::dropIfExists('integration_amocrm_user_access_tokens');
    }
}
