<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_leads', function (Blueprint $table) {
            $table->renameColumn('lead_id', 'amocrm_lead_id');
            $table->renameColumn('lead_responsible_user_id', 'amocrm_lead_responsible_user_id');
            $table->renameColumn('lead_status_id', 'amocrm_lead_status_id');
            $table->renameColumn('lead_sale', 'amocrm_lead_sale');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_leads', function (Blueprint $table) {
            $table->renameColumn('amocrm_lead_id', 'lead_id');
            $table->renameColumn('amocrm_lead_responsible_user_id', 'lead_responsible_user_id');
            $table->renameColumn('amocrm_lead_status_id', 'lead_status_id');
            $table->renameColumn('amocrm_lead_sale', 'lead_sale');
        });
    }
}
