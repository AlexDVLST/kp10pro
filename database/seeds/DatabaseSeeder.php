<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $this->call(RolesTableSeeder::class);
        $this->call(PagesTableSeeder::class);
        $this->call(CurrenciesDataTableSeeder::class);
        $this->call(ClientTypesSeeder::class);
        $this->call(TariffsSeeder::class);
        $this->call(SystemOfferStatesSeeder::class);
        $this->call(SystemCrmsSeeders::class);
        // $this->call(SystemMegaplanFieldTypesSeeder::class);
        $this->call(SystemAmocrmFieldTypesSeeder::class);
        $this->call(SystemScenarioEventsSeeder::class);
        $this->call(SystemScenarioActionsSeeder::class);
        $this->call(SystemScenarioAdditionalEventsSeeder::class);
        $this->call(SystemActionsSeeder::class);
        $this->call(HelpSectionsSeeder::class);
    }
}
