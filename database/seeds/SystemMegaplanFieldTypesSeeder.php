<?php

use Illuminate\Database\Seeder;

class SystemMegaplanFieldTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('system_megaplan_field_types')->find(1)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 1,
                    'megaplan_type'         => 'String',
                    'megaplan_content_type' => 'StringField',
                    'megaplan_type_name'    => 'Строка'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(2)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 2, 
                    'megaplan_type'         => 'Float', 
                    'megaplan_content_type' => 'FloatField',
                    'megaplan_type_name'    => 'Число'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(3)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 3, 
                    'megaplan_type'         => 'Enum', 
                    'megaplan_content_type' => 'EnumField',
                    'megaplan_type_name'    => 'Выбор'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(4)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 4, 
                    'megaplan_type'         => 'Bool', 
                    'megaplan_content_type' => 'BoolField',
                    'megaplan_type_name'    => 'Да/Нет'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(5)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 5, 
                    'megaplan_type'         => 'Date', 
                    'megaplan_content_type' => 'DateField',
                    'megaplan_type_name'    => 'Дата'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(6)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 6, 
                    'megaplan_type'         => 'DateTime', 
                    'megaplan_content_type' => 'DateTimeField',
                    'megaplan_type_name'    => 'Дата и Время'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(7)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 7,
                    'megaplan_type'         => 'RefFileList', 
                    'megaplan_content_type' => 'RefLinkField',
                    'megaplan_type_name'    => 'Файл'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(8)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 8, 
                    'megaplan_type'         => 'Money', 
                    'megaplan_content_type' => 'MoneyField',
                    'megaplan_type_name'    => 'Деньги'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(9)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 9, 
                    'megaplan_type'         => 'RefLink', 
                    'megaplan_content_type' => 'RefLinkField',
                    'megaplan_type_name'    => 'Пользователь'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(10)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 10, 
                    'megaplan_type'         => 'RefLink', 
                    'megaplan_content_type' => 'RefLinkField',
                    'megaplan_type_name'    => 'Клиент'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(11)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 11, 
                    'megaplan_type'         => 'RefLink', 
                    'megaplan_content_type' => 'RefLinkField',
                    'megaplan_type_name'    => 'Доставка'
                ]
            );
        }
        if (!DB::table('system_megaplan_field_types')->find(12)) {
            DB::table('system_megaplan_field_types')->insert(
                [
                    'id'                    => 12, 
                    'megaplan_type'         => 'BumsTradeField_ExternalSource', 
                    'megaplan_content_type' => 'ExternalSourceField',
                    'megaplan_type_name'    => 'Внешний контент'
                ]
            );
        }
    }
}
