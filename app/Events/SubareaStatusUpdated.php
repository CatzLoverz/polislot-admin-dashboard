<?php

namespace App\Events;

use App\Models\ParkSubarea;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SubareaStatusUpdated implements ShouldBroadcast
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
        
        $this->currentCount = $subarea->current_count ?? 0;
        $this->maxSlots = $subarea->max_slots ?? 0;
        $this->commentCount = $subarea->subareaComment()->count();
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('park-area.' . $this->parkAreaId),
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
