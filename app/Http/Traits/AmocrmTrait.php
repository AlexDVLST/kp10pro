<?php

namespace App\Http\Traits;

use App\Helpers\Amocrm;
use App\Models\OfferAmoCrmDeal;
use App\Models\OfferVariantProduct;
use App\Models\User;
use App\Models\Client;
use App\Models\IntegrationAmocrmUser;
use App\Models\RelationUserIntegrationAmocrmUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

trait AmocrmTrait
{
    /**
     * Add ammocrm user and employee
     *
     * @param $user
     * @param $amocrmUser
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    public function addAmocrmUser($user, $amocrmUser)
    {
        //Check if employee exist
        $employee = IntegrationAmocrmUser::whereAccountId($user->accountId)->whereAmocrmUserId($amocrmUser->id)->first();

        if ($employee) {
            //Get User model for employee
            $employee = User::whereId($employee->user_id)->first();
        } else {

            //Fix for names
            $firstName = $amocrmUser->name ? $amocrmUser->name : 'NoName';
            $lastName = $amocrmUser->last_name ? $amocrmUser->last_name : $firstName;

            $signature = $firstName . ' ' . $lastName . "\n"
                . $amocrmUser->login;

            //Prepare employee params
            $employeeParams = [
                'surname'     => $lastName,
                'name'        => $firstName,
                'middle_name' => '',
                'email'       => $amocrmUser->login,
                'phone'       => '',
                'position'    => '',
                'signature'   => $signature,
                'fileId'      => 0
            ];
            //Using trait for creating employee
            $employee = $this->addEmployee($user->domain, new Request($employeeParams), false);

            //Add amocrm user
            $amoUser = IntegrationAmocrmUser::create([
                'user_id'               => $employee->id,
                'account_id'            => $user->accountId,
                'amocrm_user_id'        => $amocrmUser->id,
                'amocrm_user_name'      => $firstName,
                'amocrm_user_last_name' => $lastName,
                'amocrm_user_login'     => $amocrmUser->login
            ]);
        }

        return $employee;
    }

    /**
     * Создание задачи
     *
     * @param $params
     */
    public function createTaskInAmoLead($scenario, $params)
    {
        $offer = $params['offer'];
        $offerId = $offer->id;
        $accountId = $offer->account_id;

        //ищем связанную сделку
        $amoDeal = OfferAmoCrmDeal::with('data')->whereOfferId($offerId)->first();

        if ($amoDeal && $amoDeal->data) {

            $scenarioEventInfo = $scenario->scenarioEvent;
            $scenarioName = $scenarioEventInfo['name'];

            $dateStart = new \DateTime('NOW');
            $dateFinish = clone $dateStart;
            $dateFinish->add(new \DateInterval('PT15M'));

            $responsible = $amoDeal->data->amocrm_lead_responsible_user_id;

            $account = User::whereId($accountId)->first()['domain'];
            $kpUrl = 'https://' . $account . '.' . env('APP_DOMAIN') . '/' . $offer->url;

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
                'add' => [
                    [
                        'element_id'          => $amoDeal->deal_id,
                        'element_type'        => 2, //тип Сделка
                        'complete_till_at'    => $dateFinish->getTimestamp(),
                        'task_type'           => 1, //тип Звонок
                        'text'                => $scenarioName.': '.$description.PHP_EOL.$kpUrl,
                        'created_at'          => $dateStart->getTimestamp(),
                        'updated_at'          => $dateStart->getTimestamp(),
                        'responsible_user_id' => $responsible,
                        'created_by'          => $responsible
                    ]
                ]
            ];

            Log::debug(print_r($model, 1));

            $Amocrm = new Amocrm($accountId);
            $Amocrm->createTask($model);
        }
    }

    /*public function setSelectOfferVariantInAmo($params)
    {
        $offer = $params['entity'];
        $offerId = $offer->id;
        $userId = $offer->user_id;
        $variants = $offer->variants()->get();
        $offerCurrency = $offer->currency()->first()->data()->first()->system()->first()->char_code;

        //ищем связанную сделку
        $amoDeal = OfferAmoCrmDeal::whereOfferId($offerId)->first();

        if ($amoDeal && $variants->isNotEmpty()) {

            $selected = '';
            $count = 0;
            foreach ($variants as $variant) {
                if ($variant['active'] && $variant['selected']) {
                    $selected = $variant;
                    break;
                }
            }

            if (!$selected) {
                return false;
            }

            //получаем список товаров по выбранному варианту, а так же их соответствия и доп. информацию
            $productsList = OfferVariantProduct::with('values', 'amocrmProduct')->whereOfferId($selected->offer_id)->whereVariantId($selected->id)->where('product_id', '<>', 0)->get();
            if ($productsList) {

                $Amocrm = new Amocrm();

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
                    }
                }

                if (!empty($rows)) {
                    $model = [
                        'id'   => $b24Deal->deal_id,
                        'rows' => $rows
                    ];

                    //Update product list in B24 Deal
                    $B24->updateProductsListInDeal($model);
                }
            }
        }
    }*/
}
