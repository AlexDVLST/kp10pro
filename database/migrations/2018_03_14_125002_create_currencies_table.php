<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->increments('id');
            $table->char('account', 100);
            $table->string('name', 100)->nullable();
            $table->integer('code')->nullable();
            $table->boolean('sync')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->char('sign', 10)->nullable();
            $table->boolean('basic', false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
