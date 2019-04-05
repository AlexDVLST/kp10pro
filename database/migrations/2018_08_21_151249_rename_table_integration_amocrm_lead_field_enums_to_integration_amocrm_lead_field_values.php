<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTableIntegrationAmocrmLeadFieldEnumsToIntegrationAmocrmLeadFieldValues extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('integration_amocrm_lead_field_enums', 'integration_amocrm_lead_field_values');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('integration_amocrm_lead_field_values', 'integration_amocrm_lead_field_enums');
    }
}
