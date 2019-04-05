<?php

namespace App\Events;

use App\Models\Offer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class OfferStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $offer;
    public $event_id;

    /**
     * Create a new event instance.
     *
     * OfferStatusChanged constructor.
     * @param Offer $offer
     * @param $eventId
     */
    public function __construct(Offer $offer, $eventId)
    {
        $this->offer = $offer;
        $this->event_id = $eventId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
