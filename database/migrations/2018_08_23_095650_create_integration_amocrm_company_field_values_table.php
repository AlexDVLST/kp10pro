<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationAmocrmCompanyFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_amocrm_company_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_field_id')->unsigned();
            $table->text('field_value');
            $table->integer('field_enum');
            $table->timestamps();

            $table->foreign('company_field_id')->references('id')->on('integration_amocrm_company_fields')
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
        Schema::dropIfExists('integration_amocrm_company_field_values');
    }
}
