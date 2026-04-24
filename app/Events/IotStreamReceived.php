<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IotStreamReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $macAddress;
    public $frameData;

    /**
     * Create a new event instance.
     */
    public function __construct($macAddress, $frameData)
    {
        $this->macAddress = $macAddress;
        $this->frameData = $frameData;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('iot.stream.' . str_replace(':', '', $this->macAddress)),
        ];
    }
    
    public function broadcastAs()
    {
        return 'stream.received';
    }
}
