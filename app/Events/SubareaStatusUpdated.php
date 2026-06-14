<?php

namespace App\Events;

use App\Models\ParkSubarea;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PhpMqtt\Client\Facades\MQTT;

class SubareaStatusUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $parkAreaId;

    public $parkSubareaId;

    public $status;

    public $statusColor;

    public $isValidated;

    public $hasUserReport;

    public $currentCount;

    public $maxSlots;

    public $commentCount;

    public $validationExpiresAt;

    public $lastValidationTime;

    public $validationRemainingSeconds;

    public $fallbackStatus;

    public $fallbackStatusColor;

    /**
     * Create a new event instance.
     */
    public function __construct(ParkSubarea $subarea)
    {
        $this->parkAreaId = $subarea->park_area_id;
        $this->parkSubareaId = $subarea->park_subarea_id;

        $live = $subarea->getLiveStatus();
        $this->status = $live['status'];
        $this->statusColor = $live['status_color'];
        $this->isValidated = $live['is_validated'];
        $this->hasUserReport = $live['has_user_report'];
        $this->validationExpiresAt = $live['validation_expires_at'] ?? null;
        $this->lastValidationTime = $live['last_validation_time'] ?? null;
        $this->validationRemainingSeconds = $live['validation_remaining_seconds'] ?? 0;
        $this->fallbackStatus = $live['fallback_status'] ?? 'netral';
        $this->fallbackStatusColor = $live['fallback_status_color'] ?? '#1572e8';

        $this->currentCount = $subarea->current_count ?? 0;
        $this->maxSlots = $subarea->max_slots ?? 0;
        $this->commentCount = $subarea->subareaComment()->count();

        // Publish ke MQTT untuk aplikasi mobile
        try {
            $mqttPayload = [
                'type' => 'status_updated',
                'parkSubareaId' => $this->parkSubareaId,
                'status' => $this->status,
                'statusColor' => $this->statusColor,
                'isValidated' => $this->isValidated,
                'hasUserReport' => $this->hasUserReport,
                'currentCount' => $this->currentCount,
                'maxSlots' => $this->maxSlots,
                'validationExpiresAt' => $this->validationExpiresAt,
                'lastValidationTime' => $this->lastValidationTime,
                'validationRemainingSeconds' => $this->validationRemainingSeconds,
                'fallbackStatus' => $this->fallbackStatus,
                'fallbackStatusColor' => $this->fallbackStatusColor,
                'commentCount' => $this->commentCount,
                'timestamp' => time()
            ];
            $mqtt = MQTT::connection('publisher');
            $mqtt->publish("frontend/parking_area/{$this->parkAreaId}", json_encode($mqttPayload), 1, true);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            Log::warning("Failed to publish status update via MQTT: " . $e->getMessage());
        }
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('park-area.'.$this->parkAreaId),
        ];
    }

    /**
     * Get the broadcast event name.
     */
    public function broadcastAs()
    {
        return 'subarea.updated';
    }
}
