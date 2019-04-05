<?php

namespace App\Jobs;

use App\Http\Controllers\ScenarioController;
use App\Http\Traits\ScenarioTrait;
use App\Models\Offer;
use App\Models\OfferAmoCrmDeal;
use App\Models\OfferBitrix24Deal;
use App\Models\OfferHistory;
use App\Models\OfferMegaplanDeal;
use App\Models\Scenario;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ClientException as HttpClientException;

class ChangedCrmDealStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ScenarioTrait;

    private $offer;
    private $scenario;

    /**
     * Create a new job instance
     *
     * ClientOpenLetterJob constructor.
     * @param Offer $offer
     * @param Scenario $scenario
     */
    public function __construct(Offer $offer, Scenario $scenario)
    {
        $this->offer = $offer;
        $this->scenario = $scenario;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $scenario = $this->scenario;
        $offer = $this->offer;

        $events = $scenario->events;
        $crmId = '';
        $programId = '';
        $crmStatusId = '';

        $execute = false;
        if ($events) {
            foreach ($events as $event) {
                switch ($event['event_type']) {
                    case 'crm':
                        $crmId = $event['event_value'];
                        break;
                    case 'program_id': //todo AIM возможно здесь не нужен
                        $programId = $event['event_value'];
                        break;
                    case 'crm_status_id':
                        $crmStatusId = $event['event_value'];
                        break;
                }
            }

            switch ($crmId) {
                case 1: //Megaplan
                    $deal = OfferMegaplanDeal::with('data')->whereOfferId($offer->id)->first();
                    $dealInfo = $deal->data;

                    //todo AIM в таблице мегаплана не хватает статуса сделки

                    if($dealInfo){
                        //Статус изменился на ...
                        if($programId == $dealInfo->megaplan_program_id && $dealInfo->megaplan_state_id == $crmStatusId){
                            $execute = true;
                        }
                    }
                    break;
                case 2: //AmoCrm
                    $deal = OfferAmoCrmDeal::with('data')->whereOfferId($offer->id)->first();
                    $dealInfo = $deal->data;

                    if($dealInfo){
                        //Статус изменился на ...
                        if($dealInfo->amocrm_lead_status_id == $crmStatusId){
                            $execute = true;
                        }
                    }
                    break;
                case 3: //Bitrix24
                    $deal = OfferBitrix24Deal::with('data')->whereOfferId($offer->id)->first();
                    $dealInfo = $deal->data;

                    if($dealInfo){
                        //Статус изменился на ...
                        if($dealInfo->bitrix24_deal_stage_id == $crmStatusId){
                            $execute = true;
                        }
                    }
                    break;
            }
        }

        if($execute){
            $this->executeEventAction($this->scenario, ['offer' => $this->offer]);
        }
    }
}
