<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_users', function (Blueprint $table) {
            $table->integer('amocrm_user_id')->after('user_id');
            $table->renameColumn('user_name', 'amocrm_user_name');
            $table->renameColumn('user_last_name', 'amocrm_user_last_name');
            $table->renameColumn('user_login', 'amocrm_user_login');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_users', function (Blueprint $table) {
            $table->dropColumn('amocrm_user_id');
            $table->renameColumn('amocrm_user_name', 'user_name');
            $table->renameColumn('amocrm_user_last_name', 'user_last_name');
            $table->renameColumn('amocrm_user_login', 'user_login');
        });
    }
}
