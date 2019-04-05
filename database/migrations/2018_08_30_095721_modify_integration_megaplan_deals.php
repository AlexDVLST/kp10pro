<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIntegrationMegaplanDeals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('integration_megaplan_deals', function (Blueprint $table) {
            $table->dropColumn('account');
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
        Schema::table('integration_megaplan_deals', function (Blueprint $table) {
            $table->dropForeign('integration_megaplan_deals_account_id_foreign');
            $table->dropColumn('account_id');
            $table->string('account', 100);
        });
    }
}
