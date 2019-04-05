<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationAmocrmCustomFieldEnumsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_amocrm_custom_field_enums', function (Blueprint $table) {
            $table->renameColumn('enum_id', 'amocrm_enum_id');
            $table->renameColumn('enum_value', 'amocrm_enum_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_amocrm_custom_field_enums', function (Blueprint $table) {
            $table->renameColumn('amocrm_enum_id', 'enum_id');
            $table->renameColumn('amocrm_enum_value', 'enum_value');
        });
    }
}
