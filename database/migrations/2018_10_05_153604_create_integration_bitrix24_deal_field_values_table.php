<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateIntegrationBitrix24DealFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('integration_bitrix24_deal_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('deal_field_id')->unsigned();
            $table->text('bitrix24_field_value');
            $table->integer('bitrix24_field_enum_id');
            $table->timestamps();

            $table->foreign('deal_field_id')->references('id')->on('integration_bitrix24_deal_fields')
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
        Schema::dropIfExists('integration_bitrix24_deal_field_values');
    }
}
