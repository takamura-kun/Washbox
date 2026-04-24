<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $location;
    public $pickupId;

    public function __construct($pickupId, $location)
    {
        $this->pickupId = $pickupId;
        $this->location = $location;
    }

    public function broadcastOn()
    {
        return new Channel('tracking');
    }

    public function broadcastAs()
    {
        return 'location.updated';
    }

    public function broadcastWith()
    {
        return [
            'type' => 'location_update',
            'pickupId' => $this->pickupId,
            'location' => $this->location,
            'timestamp' => now()->toIso8601String()
        ];
    }
}
