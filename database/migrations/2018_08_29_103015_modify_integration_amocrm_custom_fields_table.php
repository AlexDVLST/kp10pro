<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmCustomFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_custom_fields', function (Blueprint $table) {
            $table->renameColumn('field_name', 'amocrm_field_name');
            $table->renameColumn('field_id', 'amocrm_field_id');
            $table->renameColumn('field_type_id', 'amocrm_field_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_custom_fields', function (Blueprint $table) {
            $table->renameColumn('amocrm_field_name', 'field_name');
            $table->renameColumn('amocrm_field_id', 'field_id');
            $table->renameColumn('amocrm_field_type_id', 'field_type_id');
        });
    }
}
