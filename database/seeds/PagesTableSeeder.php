<?php

use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('pages')->whereSlug('offers')->first()) {
            DB::table('pages')->insert(['slug' => 'offers', 'title' => 'Кп', 'description' => 'Кп']);
        }
        if (!DB::table('pages')->whereSlug('product-custom-fields')->first()) {
            DB::table('pages')->insert(['slug' => 'product-custom-fields', 'title' => 'Дополнительные поля для товаров', 'description' => 'Дополнительные поля для товаров']);
        }
        if (!DB::table('pages')->whereSlug('offers-removed')->first()) {
            DB::table('pages')->insert(['slug' => 'offers-removed', 'title' => 'Удалённые коммерческие предложения', 'description' => 'Удалённые коммерческие предложения']);
        }
        if (!DB::table('pages')->whereSlug('products')->first()) {
            DB::table('pages')->insert(['slug' => 'products', 'title' => 'Список товаров', 'description' => 'Список товаров']);
        }
        if (!DB::table('pages')->whereSlug('file-manager')->first()) {
            DB::table('pages')->insert(['slug' => 'file-manager', 'title' => 'Файл менеджер', 'description' => 'Файл менеджер']);
        }
        if (!DB::table('pages')->whereSlug('settings-currencies')->first()) {
            DB::table('pages')->insert(['slug' => 'settings-currencies', 'title' => 'Валюты', 'description' => 'Список валют']);
        }
        if (!DB::table('pages')->whereSlug('settings-employee')->first()) {
            DB::table('pages')->insert(['slug' => 'settings-employee', 'title' => 'Сотрудники', 'description' => 'Список сотрудников']);
        }
        if (!DB::table('pages')->whereSlug('settings-employee-create')->first()) {
            DB::table('pages')->insert(['slug' => 'settings-employee-create', 'title' => 'Сотрудник', 'description' => 'Добавление сотрудника']);
        }
        if (!DB::table('pages')->whereSlug('settings-employee-edit')->first()) {
            DB::table('pages')->insert(['slug' => 'settings-employee-edit', 'title' => 'Сотрудник', 'description' => 'Редактирование сотрудника']);
        }
        if (!DB::table('pages')->whereSlug('settings-role')->first()) {
            DB::table('pages')->insert(['slug' => 'settings-role', 'title' => 'Роли', 'description' => 'Настройка ролей']);
        }
        if (!DB::table('pages')->whereSlug('client')->first()) {
            DB::table('pages')->insert(['slug' => 'client', 'title' => 'Клиенты', 'description' => 'Управление клиентами']);
        }
        if (!DB::table('pages')->whereSlug('settings-scenario')->first()) {
            DB::table('pages')->insert(['slug' => 'settings-scenario', 'title' => 'Сценарии', 'description' => 'Список сценариев']);
        }
        //Admin 
        if (!DB::table('pages')->whereSlug('admin-offers')->first()) {
            DB::table('pages')->insert(['slug' => 'admin-offers', 'title' => 'КП', 'description' => 'Управление коммерческими предложениями']);
        }
    }
}
