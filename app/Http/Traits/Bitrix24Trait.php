<?php

namespace App\Http\Traits;

use App\Helpers\Bitrix24;
use App\Models\IntegrationBitrix24Company;
use App\Models\IntegrationBitrix24Contact;
use App\Models\IntegrationBitrix24Product;
use App\Models\IntegrationBitrix24User;
use App\Models\OfferBitrix24Deal;
use App\Models\OfferVariantProduct;
use App\Models\User;
use App\Models\Client;
use App\Models\IntegrationAmocrmUser;
use App\Models\RelationUserIntegrationAmocrmUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait Bitrix24Trait
{
    /**
     * Add Bitrix24 user and employee
     *
     * @param $user
     * @param $bitrix24User
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function addBitrix24User($user, $bitrix24User)
    {
        //Check if employee exist
        $employee = IntegrationBitrix24User::whereAccountId($user->accountId)->whereBitrix24UserId($bitrix24User['ID'])->first();

        if ($employee) {
            //Get User model for employee
            $employee = User::whereId($employee->user_id)->first();
        } else {

            //Fix for names
            $firstName = $bitrix24User['NAME'] ? $bitrix24User['NAME'] : 'NoName';
            $lastName = $bitrix24User['LAST_NAME'] ? $bitrix24User['LAST_NAME'] : $firstName;

            if (is_array($bitrix24User['EMAIL'])) {
                $email = $bitrix24User['EMAIL'][0]['VALUE'];
            } else {
                $email = $bitrix24User['EMAIL'];
            }

            $signature = $firstName . ' ' . $lastName . "\n" . $email;

            //Prepare employee params
            $employeeParams = [
                'surname'     => $lastName,
                'name'        => $firstName,
                'middle_name' => '',
                'email'       => $email,
                'phone'       => '',
                'position'    => '',
                'signature'   => $signature,
                'fileId'      => 0
            ];
            //Using trait for creating employee
            $employee = $this->addEmployee($user->domain, new Request($employeeParams), false);

            //Add bitrix24 user
            IntegrationBitrix24User::create([
                'user_id'                 => $employee->id,
                'account_id'              => $user->accountId,
                'bitrix24_user_id'        => $bitrix24User['ID'],
                'bitrix24_user_name'      => $firstName,
                'bitrix24_user_last_name' => $lastName,
                'bitrix24_user_login'     => $email
            ]);
        }

        return $employee;
    }

    /**
     * Создать новую встречу
     *
     * @param $scenario
     * @param $params
     */
    public function createTaskInB24Deal($scenario, $params)
    {
        $offer = $params['offer'];
        $offerId = $offer->id;
        $accountId = $offer->account_id;

        //ищем связанную сделку
        $b24Deal = OfferBitrix24Deal::with('data')->whereOfferId($offerId)->first();

        if ($b24Deal) {

            $scenarioEventInfo = $scenario->scenarioEvent;
            $scenarioName = $scenarioEventInfo['name'];

            $dateStart = new \DateTime('NOW');
            $dateFinish = clone $dateStart;
            $dateFinish->add(new \DateInterval('PT15M'));

            $responsible = $b24Deal->data->bitrix24_deal_responsible_user_id;
            $client = $offer->clientRelation;

            $account = User::whereId($accountId)->first()['domain'];
            $kpUrl = 'https://' . $account . '.' . env('APP_DOMAIN') . '/' . $offer->url;

            $linkedClient = '';
            $linkedClientName = '';
            if ($client) {
//                $client = IntegrationBitrix24Contact::whereBitrix24ContactCompanyId($client['client_id'])->first();
                $client = IntegrationBitrix24Company::whereCompanyId($client['client_id'])->first();
//                $client = IntegrationBitrix24Contact::whereContactId($client['client_id'])->first();
                if ($client) {
//                    $linkedClient = $client['bitrix24_contact_id'];
                    $linkedClient = $client['bitrix24_company_id'];
                    $linkedClientName = $client['bitrix24_company_name'];
                }
            }

            //todo AIM вероятней всего дополнительный type - task в БД не нужен
            $actions = $scenario->actions;
            $description = '';
            if ($actions) {
                foreach ($actions as $action) {
                    if ($action['action_type'] == 'text') {
                        $description = $action['action_value'];

                        break;
                    }
                }
            }

            $events = $scenario->events;
            if ($events) {
                foreach ($events as $event) {
                    if ($event['event_type'] == 'name') {
                        $scenarioName .= ' ' . $event['event_value'];

                        break;
                    }
                }
            }

            $model = [
                'fields' => [
                    'OWNER_ID'       => $b24Deal->deal_id,
                    'OWNER_TYPE_ID'  => 2, //Сделка
                    'RESPONSIBLE_ID' => $responsible,
                    'TYPE_ID'        => 1, //Встреча
                    'COMMUNICATIONS' => [
//                            'VALUE'       => $linkedClientName,
                        'ENTITY_ID' => $linkedClient ? $linkedClient : $responsible,
//                            'ENTITY_TYPE' => 3//'COMPANY' //3 //'CONTACT'
                    ],
                    'SUBJECT'        => $scenarioName, //Название встречи (тема)
                    'DESCRIPTION'    => $description . PHP_EOL . $kpUrl . PHP_EOL, //Описание встречи (комментарий)
                    'START_TIME'     => $dateStart->format('d.m.Y H:i:s'),
                    'END_TIME'       => $dateFinish->format('d.m.Y H:i:s'),
                ]
            ];

            //Init
            $B24 = new Bitrix24($accountId);

            //Create new meeting in bitrix24 deal
            $B24->addNewTaskInDeal($model);
        }
    }

    /**
     * Обновление списка товаров в сделке
     *
     * @param $params
     * @return bool
     */
    public function setSelectOfferVariantInB24($params)
    {
        $offer = $params['offer'];
        $offerId = $offer->id;
        $userId = $offer->user_id;
        $variants = $offer->variants()->get();
        $offerCurrency = $offer->currency()->first();

        if ($offerCurrency) {
            $offerCurrency = $offerCurrency->data()->first();

            if ($offerCurrency) {
                $offerCurrency = $offerCurrency->system()->first();

                if ($offerCurrency) {
                    $offerCurrency = $offerCurrency->char_code;
                } else {
                    $offerCurrency = 'RUB';
                }
            }
        }

        //ищем связанную сделку
        $b24Deal = OfferBitrix24Deal::whereOfferId($offerId)->first();

        if ($b24Deal && $variants->isNotEmpty()) {

            $selected = '';
            $count = 0;
            foreach ($variants as $variant) {
                if ($variant['active'] && $variant['selected']) {
                    $selected = $variant;
                    break;
                }
            }

            if (!$selected) {
                Log::debug('Не удалось определить выбранный вариант КП');
                return false;
            }

            //получаем список товаров по выбранному варианту, а так же их соответствия и доп. информацию
            $productsList = OfferVariantProduct::with('values', 'bitrix24Product')->whereOfferId($selected->offer_id)->whereVariantId($selected->id)->where('product_id', '<>', 0)->get();
            if ($productsList->isNotEmpty()) {

                //Init B24
                $B24 = new Bitrix24($userId);

                //получаем список доступных валют в системе Bitrix24
                $bitrix24Currency = $B24->getCurrencies();
                if ($bitrix24Currency) {

                    $b24BaseCurrency = '';
                    $issetCurrency = false;

                    foreach ($bitrix24Currency as $b24Currency) {
                        if ($b24Currency['BASE'] == 'Y') {
                            $b24BaseCurrency = $b24Currency['CURRENCY'];
                        }

                        if ($b24Currency['CURRENCY'] == $offerCurrency) {
                            $issetCurrency = true;

                            break;
                        }
                    }
                } else {
                    Log::debug('НЕ удалось получить список валют по Битриксу');
                    return false;
                }

                $rows = [];
                foreach ($productsList as $product) {

                    $productValues = $product->values;
                    $b24Product = $product->bitrix24Product;

                    if ($productValues) {

                        $productName = '';
                        $productPrice = 0;
                        $productCount = 1;
                        $productPriceWithDiscount = 0;
                        foreach ($productValues as $value) {
                            switch ($value['type']) {
                                case 'name':
                                    $productName = $value['value'];
                                    break;
                                case 'price':
                                    $productPrice = $value['value'];
                                    break;
                                case 'count':
                                    $productCount = $value['value'];
                                    break;
                                case 'price-with-discount':
                                    $productPriceWithDiscount = $value['value'];
                                    break;
                            }
                        }

                        $productPrice = $productPriceWithDiscount ? $productPriceWithDiscount : $productPrice;

                        //Нет такого товара у нас в системе
                        if (!$b24Product) {

                            $productModel = [
                                'fields' => [
                                    'NAME'        => $productName,
                                    'CURRENCY_ID' => $issetCurrency ? $offerCurrency : $b24BaseCurrency,
                                    'PRICE'       => $productPrice
                                ]
                            ];

                            //Создаем товар в битриксе
                            $b24ProductId = $B24->createNewProduct($productModel);

                            if ($b24ProductId) {
                                //todo AIM возможно пакетно обновлять бд, а не дергать ее постоянно на запись
                                IntegrationBitrix24Product::create([
                                    'account_id'          => $userId,
                                    'product_id'          => $product->product_id,
                                    'bitrix24_product_id' => $b24ProductId
                                ]);
                            }

                            $rows[] = [
                                'PRODUCT_ID' => $b24ProductId,
                                'PRICE'      => $productPrice,
                                'QUANTITY'   => $productCount
                            ];
                        } else { //такой товар есть в нашей системе
                            $rows[] = [
                                'PRODUCT_ID' => $b24Product->bitrix24_product_id,
                                'PRICE'      => $productPrice,
                                'QUANTITY'   => $productCount
                            ];
                        }
                    }else{
                        Log::debug('$productValues - пустой');
                    }
                }

                if (!empty($rows)) {
                    $model = [
                        'id'   => $b24Deal->deal_id,
                        'rows' => $rows
                    ];

                    //Update product list in B24 Deal
                    $B24->updateProductsListInDeal($model);
                } else {
                    Log::debug('B24. Список сформированных товаров на отправку - пуст');
                }
            } else {
                Log::debug('B24. Список товаров КП пуст');
            }
        } else {
            Log::debug('B24. Не найдена сделка или не выбран вариант КП');
        }
    }
}
