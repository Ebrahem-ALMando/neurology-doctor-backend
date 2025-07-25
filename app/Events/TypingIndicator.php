<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class TypingIndicator implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $consultationId;
    public $userId;

    public function __construct($consultationId, $userId)
    {
        $this->consultationId = $consultationId;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('typing.consultation.' . $this->consultationId);
    }

    public function broadcastWith()
    {
        return [
            'consultation_id' => $this->consultationId,
            'user_id' => $this->userId,
        ];
    }
} 