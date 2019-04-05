<?php

use Illuminate\Database\Seeder;

class SystemScenarioActionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_scenario_actions')->find(1)) {
            DB::table('system_scenario_actions')->insert(['id' => 1, 'name' => 'Заполнить предварительную минимальную стоимость по КП в сделке CRM']);
        }
        if (!DB::table('system_scenario_actions')->find(2)) {
            DB::table('system_scenario_actions')->insert(['id' => 2, 'name' => 'Заполнить предварительную среднюю стоимость по КП в сделке CRM']);
        }
        if (!DB::table('system_scenario_actions')->find(3)) {
            DB::table('system_scenario_actions')->insert(['id' => 3, 'name' => 'Заполнить предварительную рекомендуемую стоимость по КП в сделке CRM']);
        }
        if (!DB::table('system_scenario_actions')->find(4)) {
            DB::table('system_scenario_actions')->insert(['id' => 4, 'name' => 'Создать дело/задачу в сделке']);
        }
        if (!DB::table('system_scenario_actions')->find(5)) {
            DB::table('system_scenario_actions')->insert(['id' => 5, 'name' => 'Заполнить заказ выбранным вариантом']);
        }
        if (!DB::table('system_scenario_actions')->find(6)) {
            DB::table('system_scenario_actions')->insert(['id' => 6, 'name' => 'Заполнить цену сделки выбранным вариантом']);
        }
        if (!DB::table('system_scenario_actions')->find(7)) {
            DB::table('system_scenario_actions')->insert(['id' => 7, 'name' => 'Изменить статус сделки в CRM']);
        }
        if (!DB::table('system_scenario_actions')->find(8)) {
            DB::table('system_scenario_actions')->insert(['id' => 8, 'name' => 'Изменить статус КП']);
        }
        if (!DB::table('system_scenario_actions')->find(9)) {
            DB::table('system_scenario_actions')->insert(['id' => 9, 'name' => 'Уведомить менеджера в Viber/Telegram']);
        }
        if (!DB::table('system_scenario_actions')->find(10)) {
            DB::table('system_scenario_actions')->insert(['id' => 10, 'name' => 'Уведомить менеджера через PUSH-уведомления']);
        }
        if (!DB::table('system_scenario_actions')->find(11)) {
            DB::table('system_scenario_actions')->insert(['id' => 11, 'name' => 'Отправить письмо на указанную почту']);
        }

    }
}
