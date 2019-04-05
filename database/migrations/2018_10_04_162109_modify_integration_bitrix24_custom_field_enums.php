<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationBitrix24CustomFieldEnums extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_bitrix24_custom_field_enums', function (Blueprint $table) {
            $table->renameColumn('enum_id', 'bitrix24_enum_id');
            $table->renameColumn('enum_value', 'bitrix24_enum_value');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_bitrix24_custom_field_enums', function (Blueprint $table) {
            $table->renameColumn('bitrix24_enum_id', 'enum_id');
            $table->renameColumn('bitrix24_enum_value', 'enum_value');
        });
    }
}
