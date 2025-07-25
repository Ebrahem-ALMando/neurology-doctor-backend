<?php

namespace App\Events;

use App\Models\ConsultationMessage;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Http\Resources\ConsultationMessageResource;

class NewConsultationMessage implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(ConsultationMessage $message)
    {
        $this->message = $message;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('consultation.' . $this->message->consultation_id);
    }

    public function broadcastWith()
    {
        return [
            'message' => new ConsultationMessageResource($this->message)
        ];
    }
} 