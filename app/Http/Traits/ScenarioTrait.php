<?php

namespace App\Http\Traits;

use App\Helpers\Amocrm;
use App\Helpers\Bitrix24;
use App\Helpers\MegaplanV3;
use App\Http\Controllers\ScenarioController;
use App\Jobs\ChangedCrmDealStatus;
use App\Jobs\ChangedKpStatus;
use App\Jobs\ClientNotOpenLetterJob;
use App\Jobs\ClientOpenLetterJob;
use App\Jobs\ManagerSavedKpJob;
use App\Jobs\ManagerSentLetter;
use App\Jobs\SelectedVariantJob;
use App\Models\Integration;
use App\Models\IntegrationMegaplan;
use App\Models\OfferAmoCrmDeal;
use App\Models\OfferBitrix24Deal;
use App\Models\OfferHistory;
use App\Models\OfferMegaplanDeal;
use App\Models\Scenario;
use App\Models\User;
use App\Models\Client;
use App\Models\Offer;
use App\Models\SystemOfferState;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\EditorController;
use App\Scopes\OfferScope;
use App\Http\Traits;

trait ScenarioTrait
{
    use MegaplanTrait, AmocrmTrait, Bitrix24Trait, OfferTrait;

    /**
     * Выполнение сценариев
     *
     * @param $eventId
     * @param $params
     */
    public function executeScenario($eventId, $params)
    {
        $offer = !empty($params['offer']) ? $params['offer'] : '';
        if (!$offer) {
            Log::debug("Offer not found");
            return;
        }

        $scenario = Scenario::with('scenarioEvent', 'scenarioAction', 'events', 'actions')->whereAccountId($offer->account_id)->whereEventId($eventId)->get();

        if ($scenario->isNotEmpty()) {

            foreach ($scenario as $item) {

                switch ($eventId) {
                    case 1: //Клиент открыл письмо
                        ClientOpenLetterJob::dispatch($params['offer'], $item);
                        break;
                    case 2: //Клиент не открыл письмо в течении

                        $delay = 0;
                        if ($item->events) {
                            foreach ($item->events as $event) {
                                if ($event['event_type'] == 'id') {
                                    $delay = $event['event_value'];
                                    break;
                                }
                            }
                        }

                        //Ставим Job на выполнение
                        ClientNotOpenLetterJob::dispatch($params['offer_history'], $params['offer'], $item)->delay(now()->addSecond($delay));
                        break;
                    case 3: //Клиент открыл письмо по ссылке
                        break;
                    case 4: //Клиент скачал Excel или PDF
                        break;
                    case 5: //Выбран вариант КП
                        SelectedVariantJob::dispatch($params['offer'], $item);
                        break;
                    case 6: //Менеджер сохранил КП
                        ManagerSavedKpJob::dispatch($params['offer'], $item);
                        break;
                    case 7: //Менеджер отправил письмо с КП
                        ManagerSentLetter::dispatch($params['offer'], $item);
                        break;
                    case 8: //Изменился статус в CRM
                        ChangedCrmDealStatus::dispatch($params['offer'], $item);
                        break;
                    case 9: //Изменился статус в КП
                        ChangedKpStatus::dispatch($params['offer'], $item);
                        break;
                }
            }
        }
    }

    /**
     * Выполнение действия по сценарию
     *
     * @param $scenario
     * @param $params
     */
    public function executeEventAction($scenario, $params)
    {
        $actionId = $scenario['action_id'];
        $crmId = '';
        if ($actionId) {

            if ($actionId <= 7) {

                $integration = Integration::whereAccountId($params['offer']->account_id)->first();

                if ($integration) {
                    $params['crm_id'] = $crmId = $integration->system_crm_id;
                } else {
                    return;
                }
            }

            switch ($actionId) {
                case 1: //Заполнить предварительную мин. стоимость по КП в сделке CRM
                case 2: //Заполнить предварительную сред. стоимость по КП в сделке CRM
                case 3: //Заполнить предварительную реком. стоимость по КП в сделке CRM
                case 6: //Заполнить цену сделки выбранным вариантом
                    $this->setDealCostFromKP($actionId, $params);
                    break;
                case 4: //Создать дело/задачу в сделке

                    switch ($crmId) {
                        case 1: // Мегаплан
                            $this->createTodoInMpDeal($scenario, $params);
                            break;
                        case 2: // AmoCRM
                            $this->createTaskInAmoLead($scenario, $params);
                            break;
                        case 3: // Bitrix24
                            $this->createTaskInB24Deal($scenario, $params);
                            break;
                    }

                    break;
                case 5: //Заполнить заказ выбранным вариантом (создание товаров)

                    switch ($crmId) {
                        case 1: // Мегаплан
                            $this->setSelectOfferVariantInMp($params);
                            break;
                        case 3: // Bitrix24
                            $this->setSelectOfferVariantInB24($params);
                            break;
                    }

                    break;
                case 7: //Изменить статус сделки в CRM
                    $this->changeCrmDealStatus($scenario, $params);
                    break;
                case 8: //Изменить статус КП

                    $actions = $scenario->actions;
                    $statusId = 0;
                    if ($actions) {
                        foreach ($actions as $action) {
                            if ($action['action_type'] == 'status_id') {
                                $statusId = $action['action_value'];

                                break;
                            }
                        }

                        $this->setOfferState($params['offer']->id, $statusId);
                    }
                    break;
                case 9: //Уведомить менеджера в Viber/Telegram
                    break;
                case 10: //Уведомить менеджера через PUSH-уведомления
                    break;
                case 11: //Отправить письмо на указанную почту
                    break;
            }
        }
}

/**
 * Изменить стоимость сделки в CRM
 *
 * @param $actionId
 * @param $params
 */
public
function setDealCostFromKP($actionId, $params)
{
    if (!empty($params['offer'])) {

        $offer = $params['offer'];
        $offerId = $offer->id;
        $accountId = $offer->account_id;
        $variants = $offer->variants()->get();

        //ищем связанную сделку
        $offerDeal = '';
        switch ($params['crm_id']) {
            case 1: // Мегаплан
                $offerDeal = OfferMegaplanDeal::whereOfferId($offerId)->first();
                break;
            case 2: // AmoCRM
                $offerDeal = OfferAmoCrmDeal::whereOfferId($offerId)->first();
                break;
            case 3: // Bitrix24
                $offerDeal = OfferBitrix24Deal::whereOfferId($offerId)->first();
                break;
        }

        if ($offerDeal && $variants->isNotEmpty()) {

            $offerCrmDealId = $offerDeal->deal_id;

            $average = 0;
            $min = 0;
            $sum = 0;
            $count = 0;
            $selected = 0;
            $recommended = 0;

            foreach ($variants as $variant) {
                if ($variant['active']) {
                    $count++;
                    $sum += $variant['price'];

                    if ($count == 1) {
                        $min = $variant['price'];
                    }

                    if ($variant['price'] < $min) {
                        $min = $variant['price'];
                    }

                    if ($variant['selected'] == 1) {
                        $selected = $variant['price'];
                    }

                    if ($variant['recommended'] == 1) {
                        $recommended = $variant['price'];
                    }
                }
            }

            if ($count) {
                $average = $sum / $count;
            }

            $price = 0;
            switch ($actionId) {
                case 1: //предварительная мин. стоимость
                    $price = $min;
                    break;
                case 2: //предварительная сред. стоимость
                    $price = $average;
                    break;
                case 3: //предварительная рек. стоимость
                    $price = $recommended;
                    break;
                case 6: //стоимость выбранного варианта
                    $price = $selected;
                    break;
            }

            switch ($params['crm_id']) {
                case 1: // Мегаплан
//                        $offerCurrency = $offer->currency()->first()->data()->first()->system()->first()->char_code;

                    $integration = IntegrationMegaplan::whereAccountId($accountId)->first();

                    if (!empty($integration)) {
                        $MegaplanV3 = new MegaplanV3(['accessToken' => $integration->api_token, 'url' => 'https://' . $integration->host]);

                        $param = [
                            'contentType' => 'Deal',
                            'cost'        => [
                                'contentType' => 'Money',
//                                'currency'    => 'RUB',
                                'value'       => $price
                            ]
                        ];

                        $MegaplanV3->updateDeal($offerCrmDealId, $param);
                    }

                    break;
                case 2: // AmoCRM

                    $dateUpdate = new \DateTime('NOW');
                    $Amocrm = new Amocrm($accountId);

                    $model = [
                        'update' => [
                            [
                                'id'         => $offerCrmDealId,
                                'sale'       => $price,
                                'updated_at' => $dateUpdate->getTimestamp(),
                            ]
                        ]
                    ];

                    $Amocrm->updateLead($model);

                    break;
                case 3: // Bitrix24
                    $model = [
                        'id'     => $offerCrmDealId,
                        'fields' => [
                            'OPPORTUNITY' => $price
                        ]
                    ];

                    //Init
                    $B24 = new Bitrix24($accountId);
                    //Update bitrix24 deal
                    $B24->updateB24Deal($model);
                    break;
            }
        }
    }
}

/**
 * Изменение статуса сделки CRM
 *
 * @param $scenario
 * @param $params
 */
public
function changeCrmDealStatus($scenario, $params)
{
    $offer = $params['offer'];
    $crmId = $params['crm_id'];
    $offerId = $offer->id;
    $accountId = $offer->account_id;

    //ищем связанную сделку
    $offerDeal = '';
    switch ($crmId) {
        case 1: // Мегаплан
            $offerDeal = OfferMegaplanDeal::whereOfferId($offerId)->first();
            break;
        case 2: // AmoCRM
            $offerDeal = OfferAmoCrmDeal::whereOfferId($offerId)->first();
            break;
        case 3: // Bitrix24
            $offerDeal = OfferBitrix24Deal::whereOfferId($offerId)->first();
            break;
    }

    if ($offerDeal) {

        $dealId = $offerDeal->deal_id;
        $actions = $scenario->actions;

        $crmStatusId = '';
        if ($actions) {
            foreach ($actions as $action) {
                if ($action['action_type'] == 'crm_status_id') {
                    $crmStatusId = $action['action_value'];

                    break;
                }
            }
        }

        if ($crmStatusId) {
            switch ($crmId) {
                case 1: // Мегаплан
                    $integration = IntegrationMegaplan::whereAccountId($accountId)->first();

                    if ($integration) {
                        $accessToken = $integration->api_token;
                        $url = 'https://' . $integration->host;

                        $MegaplanV3 = new MegaplanV3(['accessToken' => $accessToken, 'url' => $url]);

                        $model = [
                            'enabled' => true,
                            'reasons' => [],
                            'action'  => [
                                'contentType' => 'ProgramTransition',
                                'id'          => $crmStatusId
                            ]
                        ];

                        $MegaplanV3->updateDealStatus($dealId, $model);
                    }

                    break;
                case 2: // AmoCRM

                    $dateUpdate = new \DateTime('NOW');
                    $Amocrm = new Amocrm($accountId);

                    $model = [
                        'update' => [
                            [
                                'id'         => $dealId,
                                'status_id'  => $crmStatusId,
                                'updated_at' => $dateUpdate->getTimestamp(),
                            ]
                        ]
                    ];

                    $Amocrm->updateLead($model);
                    break;
                case 3: // Bitrix24

                    $model = [
                        'id'     => $dealId,
                        'fields' => [
                            'STAGE_ID' => $crmStatusId
                        ]
                    ];

                    //Init
                    $B24 = new Bitrix24($accountId);

                    //Update B24 Deal
                    $B24->updateB24Deal($model);
                    break;
            }
        }
    }
}
}
