<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationAmocrmCustomFieldEnumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_amocrm_custom_field_enums', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('custom_field_id')->unsigned();
            $table->integer('enum_id');
            $table->string('enum_value', 500);
            $table->timestamps();

            $table->foreign('custom_field_id')->references('id')->on('integration_amocrm_custom_fields')
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
        Schema::dropIfExists('integration_amocrm_custom_field_enums');
    }
}
