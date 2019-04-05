<?php

use Illuminate\Database\Seeder;

class SystemActionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_actions')->find(1)) {
            DB::table('system_actions')->insert(['id' => 1, 'text' => 'Клиент открыл письмо']);
        }
        if (!DB::table('system_actions')->find(2)) {
            DB::table('system_actions')->insert(['id' => 2, 'text' => 'Менеджер отправил письмо клиенту']);
        }
        if (!DB::table('system_actions')->find(3)) {
            DB::table('system_actions')->insert(['id' => 3, 'text' => 'Выбран вариант КП']);
        }
        if (!DB::table('system_actions')->find(4)) {
            DB::table('system_actions')->insert(['id' => 4, 'text' => 'Менеджер сохранил КП']);
        }
        if (!DB::table('system_actions')->find(5)) {
            DB::table('system_actions')->insert(['id' => 5, 'text' => 'Изменен статус КП']);
        }
        if (!DB::table('system_actions')->find(6)) {
            DB::table('system_actions')->insert(['id' => 6, 'text' => 'Изменен статус сделки в CRM']);
        }
    }
}
