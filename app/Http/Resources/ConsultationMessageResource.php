<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationMessageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'sender_id' => $this->sender_id,
            'sender_type' => $this->sender_type,
            'subject' => $this->subject,
            'message' => $this->message,
            'read_by_patient' => (bool) $this->read_by_patient,
            'read_by_doctor' => (bool) $this->read_by_doctor,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'sender' => $this->whenLoaded('sender', function () {
                return [
                    'id' => $this->sender->id,
                    'name' => $this->sender->name,
                    'role' => $this->sender->role,
                    'avatar_url' => $this->sender->avatar ? \Storage::disk('uploads')->url('users/' . $this->sender->avatar) : null,
                ];
            }),
            'attachments' => ConsultationAttachmentResource::collection($this->whenLoaded('attachments')),
        ];
    }
}
