<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event untuk mengirim command ke IoT device melalui Reverb WebSocket.
 * 
 * Ini adalah padanan WebSocket dari MQTT publish ke topic "polislot/device/{MAC}/command".
 * Python client (pysher) yang sudah join presence channel akan menerima event ini.
 */
class IotCommandSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $macAddress;
    public $action;
    public $data;
    public $signature;

    /**
     * Create a new event instance.
     *
     * @param string $macAddress  MAC address target device
     * @param string $action      Action type: 'snapshot', 'connection_test', 'chat'
     * @param array  $data        Additional payload data
     * @param string $signature   HMAC-SHA256 signature untuk verifikasi di sisi device
     */
    public function __construct(string $macAddress, string $action, array $data = [], string $signature = '')
    {
        $this->macAddress = $macAddress;
        $this->action = $action;
        $this->data = $data;
        $this->signature = $signature;
    }

    /**
     * Broadcast ke presence channel spesifik device.
     * Channel name harus sesuai dengan yang di-subscribe oleh Python client.
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('iot.device.' . str_replace(':', '', $this->macAddress)),
        ];
    }

    public function broadcastAs()
    {
        return 'command.sent';
    }
}
