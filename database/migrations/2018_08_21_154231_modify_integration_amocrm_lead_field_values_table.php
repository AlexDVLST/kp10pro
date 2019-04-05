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
            $table->dropForeign('integration_amocrm_lead_field_enums_lead_field_id_foreign');
            $table->dropColumn(['enum_id', 'enum_value']);
            $table->text('field_value');

            $table->foreign('lead_field_id')->references('id')->on('integration_amocrm_lead_fields')
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
        Schema::table('integration_amocrm_lead_field_values', function (Blueprint $table) {
            $table->dropForeign('integration_amocrm_lead_field_values_field_id_foreign');
            $table->dropColumn(['field_value', 'field_id']);
            $table->integer('enum_id');

            $table->foreign('lead_field_id')->references('id')->on('integration_amocrm_lead_fields')
                ->onDelete('cascade');
        });
    }
}
