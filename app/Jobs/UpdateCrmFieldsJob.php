<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Integration;
use App\Models\IntegrationMegaplan;
use App\Models\IntegrationMegaplanField;
use App\Models\IntegrationAmocrm;
use App\Models\IntegrationAmocrmCustomField;
use App\Helpers\Amocrm;
use App\Models\IntegrationBitrix24CustomField;
use App\Helpers\Bitrix24;
use App\Helpers\MegaplanV3;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;

class UpdateCrmFieldsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $offer;
    private $settings;
    private $user;
    private $accountId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($offer, $settings, $user)
    {
        if ($offer && $settings) {
            $this->offer     = $offer;
            $this->settings  = $settings;
            $this->user      = $user;
            $this->accountId = $user->accountId;
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() 
    {
        //Get integration settings
        $integration = Integration::withoutGlobalScope(IntegrationScope::class)->whereAccountId($this->accountId)->first();
        $systemCrmId = $integration->system_crm_id;
        $dealFields  = isset($this->settings['integration']['dealFields'])?$this->settings['integration']['dealFields']:false;
        // $offerId  = isset($this->offer->megaplanDeal->offer_id)?$this->offer->megaplanDeal->offer_id:false;
        $mDealId     = isset($this->offer->megaplanDeal->deal_id)?$this->offer->megaplanDeal->deal_id:false;
        $amoDealId   = isset($this->offer->amocrmDeal->deal_id)?$this->offer->amocrmDeal->deal_id:false;
        $b24DealId   = isset($this->offer->bitrix24Deal->deal_id)?$this->offer->bitrix24Deal->deal_id:false;

        if (!$dealFields) {return;}

        if ($systemCrmId == 1) { // Megaplan
            if (!$mDealId) {return;}
            $param = [
                'account_id' => $this->accountId
            ];
            $MegaplanV3 = new MegaplanV3($param);
            $mDealField = IntegrationMegaplanField::withoutGlobalScope(IntegrationMegaplanFieldScope::class)
                                ->whereAccountId($this->accountId)->get()->toArray();
            $mDealField = collect($mDealField);
            $params     = [];

            foreach ($dealFields as $key => $value) {

                $kpFieldId = $value['id'];
                $fieldArr  = $mDealField->filter(function ($value) use ($kpFieldId) {
                    return $value['id'] == $kpFieldId;
                })->toArray();

                $fieldArr = array_reduce($fieldArr, 'array_merge', array());
                $fieldId  = $fieldArr['field_id']; 
                if ($fieldId == 'cost') {
                    $params[$fieldId] = 
                    [
                        "contentType" => "Money",
                        "value"       => $value['value']
                    ];
                } else {
                    $params[$fieldId] = $value['value'];
                }
            }

            try {
                $MegaplanV3->updateDeal($mDealId, $params);
            } catch (Exception $e) {
                //TODO: писати помилки в БД
                Log::debug($e);
            }
        }

        if ($systemCrmId == 2) { // AmoCrm
            if (!$amoDealId) {return;}
            $account      = IntegrationAmocrm::withoutGlobalScope(IntegrationAmocrmScope::class)
                                ->whereAccountId($this->accountId)->first();
            $amoDealField = IntegrationAmocrmCustomField::withoutGlobalScope(IntegrationAmocrmCustomFieldScope::class)
                                ->whereAccountId($this->accountId)->get()->toArray();
            $amoDealField = collect($amoDealField);

            $params = [
                'account_id' => $this->accountId
            ];

            $Amocrm       = new Amocrm($params);
            $customFields = [];
            
            foreach ($dealFields as $key => $value) {
                $kpFieldId    = $value['id'];
                $kpFieldValue = $value['value'];
                
                $fieldArr     = $amoDealField->filter(function ($value) use ($kpFieldId) {
                    return $value['id'] == $kpFieldId;
                })->toArray();
                
                if (count($fieldArr) != 0) {
                    $fieldArr = array_reduce($fieldArr, 'array_merge', array());
                    $fieldId  = $fieldArr['amocrm_field_id'];

                    $customFields[] = [
                        'id'     => $fieldId,
                        'values' => [
                            [
                            'value' => html_entity_decode($kpFieldValue)
                            ]
                        ]
                    ];
                }
            }
            $updatedAt = time();
            $params    = 
            [
                'update' => 
                [
                    [
                        'id'            => $amoDealId,
                        'updated_at'    => $updatedAt,
                        'custom_fields' => $customFields
                    ]
                ]
            ];
            
            try {
                $res = $Amocrm->updateLead($params);
            } catch (Exception $e) {
                //TODO: писати помилки в БД
                Log::debug($e);
            }
        }

        if ($systemCrmId == 3) { // Bitrix24
            if (!$b24DealId) {return;}
            $params = [
                'account_id' => $this->accountId
            ];
            $Bitrix24     = new Bitrix24($params);
            $b24DealField = IntegrationBitrix24CustomField::withoutGlobalScope(IntegrationBitrix24CustomFieldScope::class)
                                ->whereAccountId($this->accountId)->get()->toArray();
            $b24DealField = collect($b24DealField);
            $params       = [];
            $fields       = [];

            foreach ($dealFields as $key => $value) {
                $kpFieldId    = $value['id'];
                $kpFieldValue = $value['value'];

                $fieldArr  = $b24DealField->filter(function ($value) use ($kpFieldId) {
                    return $value['id'] == $kpFieldId;
                })->toArray();

                $fieldArr = array_reduce($fieldArr, 'array_merge', array());
                $fieldId  = $fieldArr['bitrix24_field_id'];

                $fields[$fieldId] = $kpFieldValue;
            }

            $params = 
            [
                'id'     => $b24DealId,
                'fields' => $fields
            ];

            try {
                $Bitrix24->updateB24Deal( $params );
            } catch (Exception $e) {
                //TODO: писати помилки в БД
                Log::debug($e);
            }
        }
    }
}
