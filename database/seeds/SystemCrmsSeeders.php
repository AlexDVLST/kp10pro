<?php

use Illuminate\Database\Seeder;

class SystemCrmsSeeders extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_crms')->find(1)) {
            DB::table('system_crms')->insert(['id' => 1, 'name' => 'Megaplan', 'type' => 'megaplan']);
        }
        if (!DB::table('system_crms')->find(2)) {
            DB::table('system_crms')->insert(['id' => 2, 'name' => 'AmoCrm', 'type' => 'amocrm']);
        }
        if (!DB::table('system_crms')->find(3)) {
            DB::table('system_crms')->insert(['id' => 3, 'name' => 'Bitrix', 'type' => 'bitrix']);
        }
    }
}
