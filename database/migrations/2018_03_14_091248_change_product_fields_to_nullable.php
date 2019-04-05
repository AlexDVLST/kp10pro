<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProductFieldsToNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('article')->nullable()->change();
            $table->longText('description')->nullable()->change();
            $table->decimal('cost')->nullable()->change();
            $table->decimal('prime_cost')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('article')->nullable(false)->change();
            $table->longText('description')->nullable(false)->change();
            $table->decimal('cost')->nullable(false)->change();
            $table->decimal('prime_cost')->nullable(false)->change();
        });
    }
}
