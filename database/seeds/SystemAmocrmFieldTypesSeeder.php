<?php

use Illuminate\Database\Seeder;

class SystemAmocrmFieldTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_amocrm_field_types')->find(1)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 1,
                    'type_id'     => 1,
                    'type_code'   => 'TEXT',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(2)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 2,
                    'type_id'     => 2,
                    'type_code'   => 'NUMERIC',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(3)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 3,
                    'type_id'     => 3,
                    'type_code'   => 'CHECKBOX',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(4)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 4,
                    'type_id'     => 4,
                    'type_code'   => 'SELECT',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(5)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 5,
                    'type_id'     => 5,
                    'type_code'   => 'MULTISELECT',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(6)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 6,
                    'type_id'     => 6,
                    'type_code'   => 'DATE',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(7)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 7,
                    'type_id'     => 7,
                    'type_code'   => 'URL',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(8)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 8,
                    'type_id'     => 8,
                    'type_code'   => 'MULTITEXT',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(9)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 9,
                    'type_id'     => 9,
                    'type_code'   => 'TEXTAREA',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(10)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 10,
                    'type_id'     => 10,
                    'type_code'   => 'RADIOBUTTON',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(11)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 11,
                    'type_id'     => 11,
                    'type_code'   => 'STREETADDRESS',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(12)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 12,
                    'type_id'     => 13,
                    'type_code'   => 'SMART_ADDRESS',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(13)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 13,
                    'type_id'     => 14,
                    'type_code'   => 'BIRTHDAY',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(14)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 14,
                    'type_id'     => 15,
                    'type_code'   => 'legal_entity',
                ]
            );
        }
        if (!DB::table('system_amocrm_field_types')->find(15)) {
            DB::table('system_amocrm_field_types')->insert(
                [
                    'id'          => 15,
                    'type_id'     => 16,
                    'type_code'   => 'ITEMS',
                ]
            );
        }
    }
}
