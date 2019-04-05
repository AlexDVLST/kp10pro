<?php

use Illuminate\Database\Seeder;

class TariffsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('tariffs')->whereId(1)->first()) {
            DB::table('tariffs')->insert(['id' => 1, 'name' => '330', 'price' => 330]);
        }
    }
}
