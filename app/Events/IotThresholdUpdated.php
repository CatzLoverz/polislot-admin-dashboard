<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IotThresholdUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $macAddress;
    public $thresholdBanyak;
    public $thresholdTerbatas;

    /**
     * Create a new event instance.
     */
    public function __construct($macAddress, $thresholdBanyak, $thresholdTerbatas)
    {
        $this->macAddress = $macAddress;
        $this->thresholdBanyak = (float) $thresholdBanyak;
        $this->thresholdTerbatas = (float) $thresholdTerbatas;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        $cleanMac = str_replace(':', '', $this->macAddress);
        return [
            new Channel('iot.stream.' . $cleanMac),
        ];
    }

    public function broadcastAs()
    {
        return 'threshold.updated';
    }
}
