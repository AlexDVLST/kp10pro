<?php

namespace App\Jobs;

use App\Http\Controllers\ScenarioController;
use App\Http\Traits\ScenarioTrait;
use App\Models\Offer;
use App\Models\OfferHistory;
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

class ClientNotOpenLetterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ScenarioTrait;

    private $offer_history;
    private $offer;
    private $scenario;

    /**
     * Create a new job instance
     *
     * ClientNotOpenLetterJob constructor.
     * @param OfferHistory $offerHistory
     * @param Offer $offer
     * @param Scenario $scenario
     */
    public function __construct(OfferHistory $offerHistory, Offer $offer, Scenario $scenario)
    {
        $this->offer_history = $offerHistory;
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
        $offerHistory = $this->offer_history;
        $offerHistoryId = $offerHistory['id'];
        $offer = $this->offer;
        $client = $offer->clientRelation()->get();
        $clientId = $client[0]['client_id'];

        //проверяем выполнилось ли условие (клиент не открыл письмо в течении определенного времени)
        $searchClientAction = OfferHistory::where('id', '>', $offerHistoryId)
            ->whereAccountId($offerHistory['account_id'])
            ->whereOfferId($offerHistory['offer_id'])
            ->whereClientId($clientId)
            ->whereSystemActionId(1)->first();

        //Клиент не открыл письмо, тем самым выполнил условие
        if(!$searchClientAction){
            $this->executeEventAction($this->scenario, ['offer' => $offer]);
        }
    }
}
