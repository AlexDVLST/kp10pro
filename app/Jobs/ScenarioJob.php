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

class ScenarioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ScenarioTrait;

    private $event_id;
    private $system_action_id;
    private $user;
    private $offer;
    private $client_id;

    /**
     * Create a new job instance
     *
     * ScenarioJob constructor.
     * @param $eventId
     * @param $systemActionId
     * @param $user
     * @param Offer $offer
     */
    public function __construct($eventId, $systemActionId, $user, Offer $offer, $clientId = 0)
    {
        $this->event_id = $eventId;
        $this->system_action_id = $systemActionId;
        $this->user = $user;
        $this->offer = $offer;
        $this->client_id = $clientId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $offer = $this->offer;
        $user = $this->user;
        $eventId = $this->event_id;
        $systemActionId = $this->system_action_id;
        $clientId = $this->client_id;

        //делаем запись в таблицу offer_histories
        $offerHistory = OfferHistory::create([
            'account_id'       => $offer->account_id,
            'offer_id'         => $offer->id,
            'user_id'          => !empty($user->id) ? $user->id : 0,
            'client_id'        => $clientId,
            'system_action_id' => $systemActionId
        ]);

        $params = $eventId != 2 ? ['offer' => $offer] : ['offer' => $offer, 'offer_history' => $offerHistory];

        $this->executeScenario($eventId, $params);
    }
}
