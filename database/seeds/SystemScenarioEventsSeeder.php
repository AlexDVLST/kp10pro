<?php

use Illuminate\Database\Seeder;

class SystemScenarioEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_scenario_events')->find(1)) {
            DB::table('system_scenario_events')->insert(['id' => 1, 'name' => 'Клиент открыл письмо']);
        }
        if (!DB::table('system_scenario_events')->find(2)) {
            DB::table('system_scenario_events')->insert(['id' => 2, 'name' => 'Клиент не открыл письмо в течении']);
        }
        if (!DB::table('system_scenario_events')->find(3)) {
            DB::table('system_scenario_events')->insert(['id' => 3, 'name' => 'Клиент открыл письмо по ссылке']);
        }
        if (!DB::table('system_scenario_events')->find(4)) {
            DB::table('system_scenario_events')->insert(['id' => 4, 'name' => 'Клиент скачал Excel или Pdf']);
        }
        if (!DB::table('system_scenario_events')->find(5)) {
            DB::table('system_scenario_events')->insert(['id' => 5, 'name' => 'Выбран вариант КП']);
        }
        if (!DB::table('system_scenario_events')->find(6)) {
            DB::table('system_scenario_events')->insert(['id' => 6, 'name' => 'Менеджер сохранил КП']);
        }
        if (!DB::table('system_scenario_events')->find(7)) {
            DB::table('system_scenario_events')->insert(['id' => 7, 'name' => 'Менеджер отправил письмо с КП']);
        }
        if (!DB::table('system_scenario_events')->find(8)) {
            DB::table('system_scenario_events')->insert(['id' => 8, 'name' => 'Изменился статус в CRM']);
        }
        if (!DB::table('system_scenario_events')->find(9)) {
            DB::table('system_scenario_events')->insert(['id' => 9, 'name' => 'Изменился статус в КП10']);
        }
    }
}
