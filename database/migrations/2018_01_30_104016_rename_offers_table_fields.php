<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameOffersTableFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('offers', function (Blueprint $table) {
            $table->renameColumn('template_name', 'offer_name');
            $table->renameColumn('parent_template_id', 'parent_offer_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->renameColumn('offer_name', 'template_name');
            $table->renameColumn('parent_offer_id', 'parent_template_id');
        });
    }
}
