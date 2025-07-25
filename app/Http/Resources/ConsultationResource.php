<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'doctor_id' => $this->doctor_id,
            'status' => $this->status,
            'closed_at' => $this->closed_at,
            'last_message_at' => $this->last_message_at,
            'last_sender_id' => $this->last_sender_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'patient' => $this->whenLoaded('patient', function () {
                return [
                    'id' => $this->patient->id,
                    'name' => $this->patient->name,
                    'role' => $this->patient->role,
                    'avatar_url' => $this->patient->avatar ? \Storage::disk('public')->url('users/' . $this->patient->avatar) : null,
                ];
            }),
            'doctor' => $this->whenLoaded('doctor', function () {
                return [
                    'id' => $this->doctor->id,
                    'name' => $this->doctor->name,
                    'role' => $this->doctor->role,
                    'avatar_url' => $this->doctor->avatar ? \Storage::disk('public')->url('users/' . $this->doctor->avatar) : null,
                ];
            }),
            'last_sender' => $this->whenLoaded('lastSender', function () {
                return [
                    'id' => $this->lastSender->id,
                    'name' => $this->lastSender->name,
                    'role' => $this->lastSender->role,
                    'avatar_url' => $this->lastSender->avatar ? \Storage::disk('public')->url('users/' . $this->lastSender->avatar) : null,
                ];
            }),
            'messages' => ConsultationMessageResource::collection($this->whenLoaded('messages')),
            'attachments' => ConsultationAttachmentResource::collection($this->whenLoaded('attachments')),
            'status_logs' => ConsultationStatusLogResource::collection($this->whenLoaded('statusLogs')),
        ];
    }
}
