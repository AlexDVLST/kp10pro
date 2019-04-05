<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmLeadFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_lead_fields', function (Blueprint $table) {
            $table->integer('amocrm_lead_id')->after('field_name');
            $table->renameColumn('field_name', 'amocrm_field_name');
            $table->renameColumn('field_id', 'amocrm_field_id');
            $table->renameColumn('field_is_system', 'amocrm_field_is_system');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_lead_fields', function (Blueprint $table) {
            $table->dropColumn('amocrm_lead_id');
            $table->renameColumn('amocrm_field_name', 'field_name');
            $table->renameColumn('amocrm_field_id', 'field_id');
            $table->renameColumn('amocrm_field_is_system', 'field_is_system');
        });
    }
}
