<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationMegaplanUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_megaplan_users', function (Blueprint $table) {
            
            $table->string ('megaplan_user_middle_name', 500)->nullable()->change();
            $table->string ('megaplan_user_last_name', 500)->nullable()->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
