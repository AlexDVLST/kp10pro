<?php

namespace App\Http\Traits;

use App\Models\IntegrationMegaplanProduct;
use App\Models\Client;
use App\Models\IntegrationMegaplan;
use App\Models\OfferMegaplanDeal;
use App\Models\OfferVariantProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\IntegrationMegaplanUser;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use function GuzzleHttp\json_decode;
use Illuminate\Support\Facades\DB;
use App\Helpers\MegaplanV3;
use App\Http\Controllers\EmployeeController;
use App\Scopes\IntegrationMegaplanScope;

trait MegaplanTrait
{
    /**
     * Add megaplan user and employee
     *
     * @param $account
     * @param $accountId
     * @param $responsible
     * @param string $params
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Http\JsonResponse|null|object|string|static
     */
    public function addMegaplanUser($account, $accountId, $responsible, $params = '')
    {

        $employee = '';

        if ($responsible) {
            // foreach ($responsibles as $key => $responsible) {
            //Check if employee exist

            $email = '';
            $phone = '';
            $employee = IntegrationMegaplanUser::withoutGlobalScope(IntegrationMegaplanScope::class)
                ->whereAccountId($accountId)->whereMegaplanUserId($responsible->id)->first();

            //Using trait for creating employee
            if ($employee) {
                //Get User model for employee

                $employee = User::whereId($employee->account_id)->first();
            } else {
                try {
                    DB::transaction(function () use ($account, $accountId, $email, $phone, $responsible, &$employee, $params) {

                        $firstName = isset($responsible->firstName) ? $responsible->firstName : 'NoName';
                        $lastName = isset($responsible->lastName) ? $responsible->lastName : $firstName;
                        $middleName = isset($responsible->middleName) ? $responsible->middleName : '';

                        if (isset($responsible->contactInfo)) {
                            foreach ($responsible->contactInfo as $key => $value) {
                                if ($value->type == 'email') {
                                    $email = $value->value;
                                }
                                if ($value->type == 'phone') {
                                    $phone = $value->value;
                                }
                            }
                        }

                        $position = isset($responsible->position) ? $responsible->position : '';

                        //Prepare employee params
                        if ($email) {
                            $signature = $lastName . " " . $firstName;
                            if ($position) {
                                $signature .= "\n" . $position;
                            }
                            if ($email) {
                                $signature .= "\n" . $email;
                            }
                            if ($phone) {
                                $signature .= "\n" . $phone;
                            }

                            $employeeParams = [
                                'surname'    => $lastName,
                                'name'       => $firstName,
                                'middleName' => $middleName,
                                'email'      => $email,
                                'phone'      => $phone,
                                'position'   => $position,
                                'signature'  => $signature,
                                'fileId'     => 0
                            ];

                            $employee = $this->addEmployee($account, new Request($employeeParams), false);

                            //Add Megaplan user
                            $IntegrationMegaplanUser = IntegrationMegaplanUser::create([
                                'user_id'                   => $employee->id,
                                'account_id'                => $accountId,
                                'megaplan_user_id'          => $responsible->id,
                                'megaplan_user_name'        => $responsible->firstName,
                                'megaplan_user_middle_name' => $responsible->middleName,
                                'megaplan_user_last_name'   => $responsible->lastName
                            ]);

                            // if ($IntegrationMegaplanUser) {
                            try {
                                $pasport_token = $employee->createToken('Megaplan')->accessToken;

                                $MegaplanV3 = new MegaplanV3($params);

                                $MegaplanV3->setUser($responsible->id);

                                $res = $MegaplanV3->setUserSetting(['id' => 'kp10Token', 'value' => $pasport_token]);

                                $res = $MegaplanV3->setUserSetting(['id' => 'kp10Host', 'value' => $account . '.' . env('APP_DOMAIN')]);

                            } catch (\Exception $e) {

                                Log::debug($e->getMessage());

                                $user = Auth::user();
                                if ($user) {
                                    $id = $user->id;
                                    $account = $user->domain;

                                    // delete user
                                    $employeeController = new EmployeeController();
                                    $employeeController->destroy($account, $employee->id);
                                }

                            }
                            // }
                        }
                    });
                } catch (Exception $e) {
                    Log::debug('error add user');
                    return response()->json(['errors' => $e->getMessage()], 422);
                }
            }

            // }
        }

        return $employee;
    }

    /**
     * Создать новое дело
     *
     * @param $params
     */
    public function createTodoInMpDeal($scenario, $params)
    {
        $offer = $params['offer'];
        $offerId = $offer->id;
        $accountId = $offer->account_id;

        //ищем связанную сделку
        $mpDeal = OfferMegaplanDeal::whereOfferId($offerId)->first();

        if ($mpDeal) {

            $integration = IntegrationMegaplan::whereAccountId($accountId)->first();

            if (!empty($integration)) {

                $scenarioEventInfo = $scenario->scenarioEvent;
                $scenarioName = $scenarioEventInfo['name'];

                $dateStart = new \DateTime('NOW');
                $dateFinish = clone $dateStart;
                $dateFinish->add(new \DateInterval('PT15M'));

                $MegaplanV3 = new MegaplanV3(['accessToken' => $integration->api_token, 'url' => 'https://' . $integration->host]);

                $dealCard = $MegaplanV3->getDeal($mpDeal->deal_id);
                $responsible = isset($dealCard->manager->id) ? $dealCard->manager->id : '';
                $contractor = isset($dealCard->contractor->id) ? $dealCard->contractor->id : '';

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
                    'name'         => $scenarioName,
                    'description'  => $description . PHP_EOL . $kpUrl,
                    'category'     => [ //категория дела
                        'contentType' => 'TodoCategory',
                        'id'          => 1 // 1 - звонок , 6 - дело
                    ],
                    'participants' => [ //участники
                        [
                            'contentType' => 'Participant',
                            'subject'     => [
                                'contentType' => 'ContractorHuman',
                                'id'          => $contractor
                            ]
                        ]
                    ],
                    'responsible'  => [ //ответственный
                        'contentType' => 'Employee',
                        'id'          => $responsible
                    ],
                    'when'         => [ //когда
                        'contentType' => 'IntervalTime',
                        'from'        => [
                            'contentType' => 'DateTime',
                            'value'       => $dateStart->format('Y-m-d H:i:s.u T')
                        ],
                        'to'          => [
                            'contentType' => 'DateTime',
                            'value'       => $dateFinish->format('Y-m-d H:i:s.u T')
                        ]
                    ],
                    'relations'    => [ //связь с сущностью
                        [
                            'contentType' => 'Deal',
                            'id'          => $mpDeal->deal_id
                        ]
                    ]
                ];

                $MegaplanV3->createTodo($model);
            }
        }
    }

    /**
     * Обновить список товаров в сделке
     *
     * @param $params
     * @return bool
     */
    public function setSelectOfferVariantInMp($params)
    {
        $offer = $params['offer'];
        $offerId = $offer->id;
        $accountId = $offer->account_id;
        $variants = $offer->variants()->get();
        $offerCurrency = $offer->currency()->first();

        if($offerCurrency){
            $offerCurrency = $offerCurrency->data()->first();

            if($offerCurrency){
                $offerCurrency = $offerCurrency->system()->first();

                if($offerCurrency){
                    $offerCurrency = $offerCurrency->char_code;
                }else{
                    $offerCurrency = 'RUB';
                }
            }
        }

        Log::debug(print_r(['Валюта КП: ' => $offerCurrency], 1));

        //ищем связанную сделку
        $mpDeal = OfferMegaplanDeal::whereOfferId($offerId)->first();

        if ($mpDeal && $variants->isNotEmpty()) {

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
            $productsList = OfferVariantProduct::with('values', 'megaplanProduct')->whereOfferId($selected->offer_id)->whereVariantId($selected->id)->where('product_id', '<>', 0)->get();

            if ($productsList) {

                $integration = IntegrationMegaplan::first();

                if (!empty($integration)) {

                    $MegaplanV3 = new MegaplanV3(['accessToken' => $integration->api_token, 'url' => 'https://' . $integration->host]);

                    //todo (возможно) получаем список доступных валют в системе Мегаплана

                    $positions = [];
                    foreach ($productsList as $product) {
                        $productValues = $product->values;
                        $megaplanProduct = $product->megaplanProduct;

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
                                        $productCount = (int)$value['value'];
                                        break;
                                    case 'price-with-discount':
                                        $productPriceWithDiscount = $value['value'];
                                        break;
                                }
                            }

                            $productPrice = $productPriceWithDiscount ? $productPriceWithDiscount : $productPrice;

                            //Такого товара нет в нашей системе
                            if (!$megaplanProduct) {

                                $productModel = [
                                    'name'  => $productName,
                                    'price' => [
                                        'contentType' => 'Money',
                                        'currency'    => $offerCurrency,
                                        'value'       => $productPrice
                                    ],
                                    'unit'  => [
                                        'contentType' => 'Unit',
                                        'id'          => 1
                                    ]
                                ];

                                //Создаем товар в Мегаплане
                                $mpProduct = $MegaplanV3->createNewProduct($productModel);

                                if ($mpProduct->id) {
                                    //todo AIM возможно пакетно обновлять бд, а не дергать ее постоянно на запись
                                    IntegrationMegaplanProduct::create([
                                        'account_id'          => $accountId,
                                        'product_id'          => $product->product_id,
                                        'megaplan_product_id' => $mpProduct->id
                                    ]);
                                }

                                $positions[] = [
                                    'contentType' => 'OfferRow',
                                    'name'        => $productName,
                                    'deal'        => [
                                        'contentType' => 'Deal',
                                        'id'          => $mpDeal->deal_id
                                    ],
                                    'discount'    => [
                                        'contentType' => 'Discount',
                                        'type'        => 'absolute',
                                        'valueInMain' => 0
                                    ],
                                    'margin'      => [
                                        'contentType' => 'Discount',
                                        'type'        => 'absolute',
                                        'valueInMain' => 0
                                    ],
                                    'offer'       => [
                                        'contentType' => 'Offer',
                                        'id'          => $mpProduct->id
                                    ],
                                    'price'       => [
                                        'contentType' => 'Money',
                                        'currency'    => $offerCurrency,
                                        'value'       => $productPrice,
                                        'rate'        => 1,
                                        'valueInMain' => $productPrice
                                    ],
                                    'quantity'    => $productCount,
                                    'unit'        => [
                                        'contentType' => 'Unit',
                                        'id'          => 1
                                    ]
                                ];
                            } else { //такой товар есть в нашей системе

                                $positions[] = [
                                    'contentType' => 'OfferRow',
                                    'price'       => [
                                        'value'       => $productPrice,
                                        'currency'    => $offerCurrency,
                                        'rate'        => 1,
                                        'valueInMain' => $productPrice,
                                        'contentType' => 'Money'
                                    ],
                                    'name'        => $productName,
                                    'quantity'    => $productCount,
                                    'discount'    => [
                                        'type'        => 'absolute',
                                        'valueInMain' => 0,
                                        'contentType' => 'Discount'
                                    ],
                                    'margin'      => [
                                        'type'        => 'absolute',
                                        'valueInMain' => 0,
                                        'contentType' => 'Discount'
                                    ],
                                    'unit'        => [
                                        'id'          => 1,
                                        'contentType' => 'Unit'
                                    ],
                                    'offer'       => [
                                        'id'          => $megaplanProduct->megaplan_product_id,
                                        'contentType' => 'Offer'
                                    ],
                                    'deal'        => [
                                        'id'          => $mpDeal->deal_id,
                                        'contentType' => 'Deal'
                                    ]
                                ];
                            }
                        }
                    }

                    if (!empty($positions)) {
                        $model = [
                            'contentType' => 'Deal',
                            'id'          => $mpDeal->deal_id,
                            'positions'   => $positions
                        ];

                        //Update product list in B24 Deal
                        $MegaplanV3->updateDeal($mpDeal->deal_id, $model);
                    }else{
                        Log::debug('MP. Список сформированных товаров на отправку - пуст');
                    }
                }else{
                    Log::debug('MP. Интеграция с Мегапланом не настроена');
                }
            }else{
                Log::debug('MP. Список товаров КП пуст');
            }
        }else{
            Log::debug('MP. Не найдена сделка или не выбран вариант КП');
        }
    }
}
