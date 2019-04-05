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
            $table->dropColumn(['account', 'field_type_id']);
            $table->boolean('field_is_system');
            $table->integer('account_id')->unsigned();

            $table->foreign('account_id')->references('id')->on('users')
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
        Schema::table('integration_amocrm_lead_fields', function (Blueprint $table) {
            $table->dropForeign('integration_amocrm_lead_fields_account_id_foreign');
            $table->dropColumn(['field_is_system', 'account_id']);
            $table->integer('field_type_id');
            $table->string('account', 200);
        });
    }
}
