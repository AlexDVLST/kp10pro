<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUrlFieldAndAddStaticField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
	public function up()
	{
		Schema::table('templates', function (Blueprint $table) {
			$table->string('url', 10)->nullable()->comment('Короткая ссылка на кп');
			$table->boolean('basic')->nullable()->comment('Является ли базовым шаблоном');
			$table->text('parent_template_id')->nullable()->comment('Родительский шаблон');
		});
	}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
	    Schema::table('templates', function (Blueprint $table) {
		    $table->dropColumn('url');
		    $table->dropColumn('basic');
		    $table->dropColumn('parent_template_id');
	    });
    }
}
