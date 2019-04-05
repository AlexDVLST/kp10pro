<?php

use Illuminate\Database\Seeder;

class SystemOfferStatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_offer_states')->whereId(1)->first()) {
            DB::table('system_offer_states')->insert(['id' => 1, 'name' => 'Создано', 'color' => 'purple']);
        }
        if (!DB::table('system_offer_states')->whereId(2)->first()) {
            DB::table('system_offer_states')->insert(['id' => 2, 'name' => 'Отправлено', 'color' => 'primary']);
        }
        if (!DB::table('system_offer_states')->whereId(3)->first()) {
            DB::table('system_offer_states')->insert(['id' => 3, 'name' => 'Просмотрено', 'color' => 'maroon']);
        }
        if (!DB::table('system_offer_states')->whereId(4)->first()) {
            DB::table('system_offer_states')->insert(['id' => 4, 'name' => 'Вариант выбран', 'color' => 'orange']);
        }
        if (!DB::table('system_offer_states')->whereId(5)->first()) {
            DB::table('system_offer_states')->insert(['id' => 5, 'name' => 'Продано', 'color' => 'green']);
        }
        if (!DB::table('system_offer_states')->whereId(6)->first()) {
            DB::table('system_offer_states')->insert(['id' => 6, 'name' => 'Отказ', 'color' => 'red']);
        }
        
    }
}
