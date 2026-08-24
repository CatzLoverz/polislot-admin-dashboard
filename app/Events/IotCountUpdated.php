<?php

namespace App\Events;

use App\Models\IotDevice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IotCountUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $macAddress;

    public $count;

    /**
     * Create a new event instance.
     */
    public function __construct($macAddress, $count)
    {
        $this->macAddress = $macAddress;
        $this->count = (int) $count;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $cleanMac = IotDevice::normalizeMac($this->macAddress);

        return [
            new Channel('iot.detection.'.$cleanMac),
        ];
    }

    public function broadcastAs()
    {
        return 'count.updated';
    }
}
