<?php

namespace App\Listeners;

use App\Events\OfferStatusChanged;
use App\Http\Controllers\ScenarioController;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class OfferStatusChangedSubscriber
{
    /**
     * Create the event listener.
     *
     * OfferStatusChangedSubscriber constructor.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OfferStatusChanged  $event
     * @return void
     */
    public function handle(OfferStatusChanged $event)
    {
        $scenario = new ScenarioController();

        $scenario->executeScenario($event->event_id, $event->offer);
    }
}
