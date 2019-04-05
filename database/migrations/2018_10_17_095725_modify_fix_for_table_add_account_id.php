<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFixForTableAddAccountId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
        });

        Schema::table('currencies', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
        });

        Schema::table('offers', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
        });

        Schema::table('product_custom_fields', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->integer('account_id')->unsigned();

            // $table->foreign('account_id')->references('id')->on('users')
            //     ->onDelete('cascade');
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
