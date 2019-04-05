<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationAmocrmContactFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_amocrm_contact_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('contact_field_id')->unsigned();
            $table->text('field_value');
            $table->integer('field_enum');
            $table->timestamps();

            $table->foreign('contact_field_id')->references('id')->on('integration_amocrm_contact_fields')
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
        Schema::dropIfExists('integration_amocrm_contact_field_values');
    }
}
