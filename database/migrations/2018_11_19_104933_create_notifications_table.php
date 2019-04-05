<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->text('text')->nullable(); //may be null only if view exist
            $table->integer('type_id'); // 1 - show as popup after login (using for ADS or else), 2 - integration CRM error/warning/messages
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->string('view')->nullable(); //External view for message (using with type_id = 1)
            $table->tinyInteger('viewed')->default(0);
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
        Schema::dropIfExists('messages');
    }
}
