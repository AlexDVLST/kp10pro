<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_companies', function (Blueprint $table) {
            $table->renameColumn('company_name', 'amocrm_company_name');
            $table->dropColumn('company_responsible_user_id');
            $table->integer('amocrm_company_id')->after('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_companies', function (Blueprint $table) {
            $table->dropColumn('amocrm_company_id');
            $table->renameColumn('amocrm_company_name', 'company_name');
            $table->string('company_responsible_user_id', 500);
        });
    }
}
