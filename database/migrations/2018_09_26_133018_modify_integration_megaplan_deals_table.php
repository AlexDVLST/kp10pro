<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationMegaplanDealsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_megaplan_deals', function (Blueprint $table) {
            $table->renameColumn('deal_id', 'megaplan_deal_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('integration_megaplan_deals', function (Blueprint $table) {
            $table->renameColumn('megaplan_deal_id', 'deal_id');
        });
    }
}
