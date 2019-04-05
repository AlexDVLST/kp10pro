<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmLeadFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_lead_field_values', function (Blueprint $table) {
            $table->renameColumn('field_value', 'amocrm_field_value');
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
            $table->renameColumn('amocrm_field_value', 'field_value');
        });
    }
}
