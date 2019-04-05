<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id');
            $table->char('account', 100);
            $table->string('name');
            $table->string('article');
            $table->longText('prod_description');
            $table->decimal('cost',10,2);
            $table->decimal('prime_cost',10,2);
            $table->boolean('removed')->nullable()->comment('Флаг удалённого коммерческого предложения'); //TODO: переписати з використанням deleted_at
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
        Schema::dropIfExists('products');
    }
}
