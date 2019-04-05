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

class ManagerSentLetter implements ShouldQueue
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
        $this->executeEventAction($this->scenario, ['offer' => $this->offer]);
    }
}
