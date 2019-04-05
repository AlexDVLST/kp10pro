<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnToIntegrationAmocrmLeadFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_lead_field_values', function (Blueprint $table) {
            $table->integer('amocrm_field_enum_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_lead_field_values', function (Blueprint $table) {
            $table->$table->dropColumn('amocrm_field_enum_id');
        });
    }
}
