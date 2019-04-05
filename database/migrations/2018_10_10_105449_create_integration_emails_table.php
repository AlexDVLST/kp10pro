<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('smtp_login');
            $table->string('smtp_password');
            $table->string('smtp_server');
            $table->integer('smtp_port');
            $table->boolean('smtp_secure');
            $table->integer('user_id'); //Employee user id
            $table->integer('account_id')->unsigned();
            $table->timestamps();

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
        Schema::dropIfExists('integration_emails');
    }
}
