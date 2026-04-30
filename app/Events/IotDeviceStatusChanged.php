<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IotDeviceStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $macAddress;
    public $status;

    /**
     * Create a new event instance.
     */
    public function __construct($macAddress, $status)
    {
        $this->macAddress = $macAddress;
        $this->status = $status; // 'online' atau 'offline'
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        // Broadcast ke channel umum status agar UI bisa update dot/badge
        return [
            new Channel('iot.status'),
        ];
    }

    public function broadcastAs()
    {
        return 'device.status';
    }
}
