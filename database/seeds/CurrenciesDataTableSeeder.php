<?php

use Illuminate\Database\Seeder;

class CurrenciesDataTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!DB::table('currencies_data')->whereCharCode('USD')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'USD', 'code' => '840', 'description' => 'Доллар США', 'rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('RUB')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'RUB', 'code' => '643', 'description' => 'Российский рубль','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('UAH')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'UAH', 'code' => '980', 'description' => 'Украинская гривна','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('EUR')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'EUR', 'code' => '978', 'description' => 'Евро', 'rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('GBP')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'GBP', 'code' => '826', 'description' => 'Фунт стерлингов Велико­британии','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('JPY')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'JPY', 'code' => '392', 'description' => 'Японская йена','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('CHF')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'CHF', 'code' => '756', 'description' => 'Швейцарский франк','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('CNY')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'CNY', 'code' => '156', 'description' => 'Китайский юань женьминьби','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('BYN')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'BYN', 'code' => '933', 'description' => 'Белорусский рубль','rate' => '1']);
        }
        if (!DB::table('currencies_data')->whereCharCode('KZT')->first()) {
            DB::table('currencies_data')->insert(['char_code' => 'KZT', 'code' => '398', 'description' => 'Казахский тенге','rate' => '1']);
        }

//        if (!DB::table('currencies_data')->whereCharCode('AED')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'AED', 'code' => '784', 'description' => 'Дирхам ОАЭ','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('AFN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'AFN', 'code' => '971', 'description' => 'Афганский афгани','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ALL')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ALL', 'code' => '008', 'description' => 'Албанский лек','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('AMD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'AMD', 'code' => '051', 'description' => 'Армянский драм','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('AOA')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'AOA', 'code' => '973', 'description' => 'Ангольская кванза','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ARS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ARS', 'code' => '032', 'description' => 'Аргентинский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('AUD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'AUD', 'code' => '036', 'description' => 'Австралийский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('AZN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'AZN', 'code' => '944', 'description' => 'Азербайджанский манат','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BDT')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BDT', 'code' => '050', 'description' => 'Бангладешская така','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BGN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BGN', 'code' => '975', 'description' => 'Болгарский лев','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BHD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BHD', 'code' => '048', 'description' => 'Бахрейнский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BIF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BIF', 'code' => '108', 'description' => 'Бурундийский франк','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BND')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BND', 'code' => '096', 'description' => 'Брунейский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BOB')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BOB', 'code' => '068', 'description' => 'Боливийский боливиано','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BRL')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BRL', 'code' => '068', 'description' => 'Бразильский реал','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BWP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BWP', 'code' => '072', 'description' => 'Ботсванская пула','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('BYN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'BYN', 'code' => '933', 'description' => 'Белорусский рубль','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CAD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CAD', 'code' => '124', 'description' => 'Канадский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CDF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CDF', 'code' => '976', 'description' => 'Конголезский франк','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CLP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CLP', 'code' => '152', 'description' => 'Чилийский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('COP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'COP', 'code' => '170', 'description' => 'Колумбийский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CRC')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CRC', 'code' => '188', 'description' => 'Костариканский колон','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CUP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CUP', 'code' => '192', 'description' => 'Кубинский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CZK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CZK', 'code' => '203', 'description' => 'Чешская крона','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('CZK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'CZK', 'code' => '203', 'description' => 'Чешская крона','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('DJF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'DJF', 'code' => '262', 'description' => 'Джибутийский франк','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('DKK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'DKK', 'code' => '208', 'description' => 'Датская крона','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('DZD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'DZD', 'code' => '012', 'description' => 'Алжирский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('EGP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'EGP', 'code' => '818', 'description' => 'Египетский фунт','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ETB')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ETB', 'code' => '230', 'description' => 'Эфиопский быр','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('GEL')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'GEL', 'code' => '981', 'description' => 'Грузинский лари','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('GHS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'GHS', 'code' => '936', 'description' => 'Ганский седи','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('GMD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'GMD', 'code' => '270', 'description' => 'Гамбийский даласи','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('GNF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'GNF', 'code' => '324', 'description' => 'Гвинейский франк','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('HKD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'HKD', 'code' => '344', 'description' => 'Гонконгский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('HRK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'HRK', 'code' => '191', 'description' => 'Хорватская куна','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('HUF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'HUF', 'code' => '348', 'description' => 'Венгерский форинт','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('IDR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'IDR', 'code' => '360', 'description' => 'Индонезийская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ILS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ILS', 'code' => '376', 'description' => 'Израильский шекель','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('INR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'INR', 'code' => '356', 'description' => 'Индийская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('IQD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'IQD', 'code' => '368', 'description' => 'Иракский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('IRR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'IRR', 'code' => '364', 'description' => 'Иранский риал','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ISK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ISK', 'code' => '352', 'description' => 'Исландская крона','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('JOD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'JOD', 'code' => '400', 'description' => 'Иорданский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KES')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KES', 'code' => '404', 'description' => 'Кенийский шиллинг','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KGS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KGS', 'code' => '417', 'description' => 'Киргизский сом','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KHR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KHR', 'code' => '116', 'description' => 'Камбоджийский риель','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KPW')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KPW', 'code' => '408', 'description' => 'Северо-корейская вона (КНДР)','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KRW')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KRW', 'code' => '410', 'description' => 'Южно-корейская вона (Корея)','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KWD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KWD', 'code' => '414', 'description' => 'Кувейтский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('KZT')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'KZT', 'code' => '398', 'description' => 'Казахский тенге','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('LAK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'LAK', 'code' => '418', 'description' => 'Лаосский кип','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('LBP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'LBP', 'code' => '422', 'description' => 'Ливанский фунт','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('LKR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'LKR', 'code' => '144', 'description' => 'Шри-ланкийская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('LYD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'LYD', 'code' => '434', 'description' => 'Ливийский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MAD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MAD', 'code' => '504', 'description' => 'Марокканский дирхам','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MDL')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MDL', 'code' => '498', 'description' => 'Молдовский лей','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MGA')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MGA', 'code' => '969', 'description' => 'Малагасийский ариари','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MKD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MKD', 'code' => '807', 'description' => 'Македонский денар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MNT')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MNT', 'code' => '496', 'description' => 'Монгольский тугрик','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MRO')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MRO', 'code' => '478', 'description' => 'Мавританская угия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MUR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MUR', 'code' => '480', 'description' => 'Маврикийская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MWK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MWK', 'code' => '454', 'description' => 'Малавийская квача','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MXN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MXN', 'code' => '484', 'description' => 'Мексиканский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MYR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MYR', 'code' => '458', 'description' => 'Малайзийский ринггит','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('MZN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'MZN', 'code' => '943', 'description' => 'Мозамбикский метикал','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('NAD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'NAD', 'code' => '516', 'description' => 'Намибийский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('NGN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'NGN', 'code' => '566', 'description' => 'Нигерийская наира','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('NIO')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'NIO', 'code' => '558', 'description' => 'Никарагуанская кордоба','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('NOK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'NOK', 'code' => '578', 'description' => 'Норвежская крона','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('NPR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'NPR', 'code' => '524', 'description' => 'Непальская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('NZD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'NZD', 'code' => '554', 'description' => 'Ново­зеландский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('OMR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'OMR', 'code' => '512', 'description' => 'Оманский риал','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('PEN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'PEN', 'code' => '604', 'description' => 'Перуанский соль','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('PHP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'PHP', 'code' => '608', 'description' => 'Филиппинский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('PKR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'PKR', 'code' => '586', 'description' => 'Пакистанская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('PLN')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'PLN', 'code' => '985', 'description' => 'Польский злотый','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('PYG')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'PYG', 'code' => '600', 'description' => 'Парагвайский гуарани','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('QAR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'QAR', 'code' => '634', 'description' => 'Катарский риал','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('RON')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'RON', 'code' => '946', 'description' => 'Новый румынский лей','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('RSD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'RSD', 'code' => '941', 'description' => 'Сербский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SAR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SAR', 'code' => '682', 'description' => 'Саудовский риял','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SCR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SCR', 'code' => '690', 'description' => 'Сейшельская рупия','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SDG')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SDG', 'code' => '938', 'description' => 'Суданский фунт','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SEK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SEK', 'code' => '752', 'description' => 'Шведская крона','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SGD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SGD', 'code' => '702', 'description' => 'Сингапурский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SLL')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SLL', 'code' => '694', 'description' => 'Сьерра-леонский леоне','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SOS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SOS', 'code' => '706', 'description' => 'Сомалийский шиллинг','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SRD')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SRD', 'code' => '968', 'description' => 'Суринамский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SYP')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SYP', 'code' => '760', 'description' => 'Сирийский фунт','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('SZL')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'SZL', 'code' => '748', 'description' => 'Свазилендский лилангени','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('THB')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'THB', 'code' => '764', 'description' => 'Таиландский бат','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('TJS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'TJS', 'code' => '972', 'description' => 'Таджикский сомони','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('TMT')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'TMT', 'code' => '795', 'description' => 'Туркменский манат','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('TND')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'TND', 'code' => '788', 'description' => 'Тунисский динар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('TRY')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'TRY', 'code' => '949', 'description' => 'Новая турецкая лира','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('TRY')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'TWD', 'code' => '901', 'description' => 'Тайваньский доллар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('TZS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'TZS', 'code' => '834', 'description' => 'Танзанийский шиллинг','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('UGX')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'UGX', 'code' => '800', 'description' => 'Угандийский шиллинг','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('UYU')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'UYU', 'code' => '858', 'description' => 'Уругвайский песо','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('UZS')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'UZS', 'code' => '860', 'description' => 'Узбекский сум','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('VEF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'VEF', 'code' => '937', 'description' => 'Венесуэльский боливар','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('VND')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'VND', 'code' => '704', 'description' => 'Вьетнамский донг','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('XAF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'XAF', 'code' => '950', 'description' => 'Франк КФА (Центральная Африка)','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('XDR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'XDR', 'code' => '960', 'description' => 'СПЗ','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('XOF')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'XOF', 'code' => '952', 'description' => 'Франк КФА (Западная Африка)','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('YER')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'YER', 'code' => '886', 'description' => 'Йеменский риал','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ZAR')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ZAR', 'code' => '710', 'description' => 'Южно-африканский рэнд','rate' => '1']);
//        }
//        if (!DB::table('currencies_data')->whereCharCode('ZMK')->first()) {
//            DB::table('currencies_data')->insert(['char_code' => 'ZMK', 'code' => '894', 'description' => 'Замбийская квача','rate' => '1']);
//        }
    }

}
