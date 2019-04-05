<?php

use Illuminate\Database\Seeder;

class ClientTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('client_types')->find(1)) {
            DB::table('client_types')->insert(['id' => 1, 'name' => 'Компания']);
        }
        if (!DB::table('client_types')->find(2)) {
            DB::table('client_types')->insert(['id' => 2, 'name' => 'Человек']);
        }
        if (!DB::table('client_types')->find(3)) {
            DB::table('client_types')->insert(['id' => 3, 'name' => 'Контактное лицо']);
        }
    }
}
