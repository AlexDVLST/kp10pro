<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationMegaplanFieldValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_megaplan_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('account_id')->unsigned();
            $table->integer('field_id'); // id поля з таблички integration_megaplan_fields
            $table->integer('deal_id'); // id поля з таблички offer_megaplan_deals
            $table->string('megaplan_field_values');
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
        Schema::dropIfExists('integration_megaplan_field_values');
    }
}
