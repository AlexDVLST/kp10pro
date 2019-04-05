<?php

use Illuminate\Database\Seeder;

class HelpSectionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('help_sections')->whereId(1)->first()) {
            DB::table('help_sections')->insert(['id' => 1, 'name' => 'Редактор']);
        }
        if (!DB::table('help_sections')->whereId(2)->first()) {
            DB::table('help_sections')->insert(['id' => 2, 'name' => 'Настройки']);
        }
        if (!DB::table('help_sections')->whereId(3)->first()) {
            DB::table('help_sections')->insert(['id' => 3, 'name' => 'Справочники']);
        }
        if (!DB::table('help_sections')->whereId(4)->first()) {
            DB::table('help_sections')->insert(['id' => 4, 'name' => 'FAQ']);
        }
        if (!DB::table('help_sections')->whereId(5)->first()) {
            DB::table('help_sections')->insert(['id' => 5, 'name' => 'Примеры КП']);
        }
    }
}
