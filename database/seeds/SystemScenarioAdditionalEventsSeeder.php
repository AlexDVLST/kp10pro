<?php

use Illuminate\Database\Seeder;

class SystemScenarioAdditionalEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_scenario_additional_events')->find(1)) {
            DB::table('system_scenario_additional_events')->insert([
                    'event_id' => 2,
                    'additional_event_name' => '3 часов',
                    'additional_event_value' => '10800',
                    'type' => 'time_2'
                ]);
        }
        if (!DB::table('system_scenario_additional_events')->find(2)) {
            DB::table('system_scenario_additional_events')->insert([
                'event_id' => 2,
                'additional_event_name' => '6 часов',
                'additional_event_value' => '21600',
                'type' => 'time_2'
            ]);
        }
        if (!DB::table('system_scenario_additional_events')->find(3)) {
            DB::table('system_scenario_additional_events')->insert([
                'event_id' => 2,
                'additional_event_name' => 'дня',
                'additional_event_value' => '86400',
                'type' => 'time_2'
            ]);
        }
        if (!DB::table('system_scenario_additional_events')->find(4)) {
            DB::table('system_scenario_additional_events')->insert([
                'event_id' => 2,
                'additional_event_name' => '2 дней',
                'additional_event_value' => '172800',
                'type' => 'time_2'
            ]);
        }
        if (!DB::table('system_scenario_additional_events')->find(5)) {
            DB::table('system_scenario_additional_events')->insert([
                'event_id' => 2,
                'additional_event_name' => '3 дней',
                'additional_event_value' => '259200',
                'type' => 'time_2'
            ]);
        }
    }
}
