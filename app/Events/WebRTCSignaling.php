<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebRTCSignaling implements ShouldBroadcast
{
    public $data;
    public $roomId;

    public function __construct($data, $roomId)
    {
        $this->data = $data;
        $this->roomId = $roomId;
    }

    public function broadcastOn()
    {
        return ['webrtc-room-' . $this->roomId];
    }
}

