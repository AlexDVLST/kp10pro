<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_contacts', function (Blueprint $table) {
            $table->dropColumn('contact_responsible_user_id');
            $table->renameColumn('contact_name', 'amocrm_contact_name');
            $table->renameColumn('contact_company_id', 'amocrm_contact_company_id');
            $table->integer('amocrm_contact_id')->after('contact_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_contacts', function (Blueprint $table) {
            $table->dropColumn('amocrm_contact_id');
            $table->renameColumn('amocrm_contact_name', 'contact_name');
            $table->renameColumn('amocrm_contact_company_id', 'contact_company_id');
            $table->integer('contact_responsible_user_id');
        });
    }
}
