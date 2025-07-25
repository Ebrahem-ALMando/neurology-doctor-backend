<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ConsultationStatusLogResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'consultation_id' => $this->consultation_id,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'changed_by_id' => $this->changed_by_id,
            'changed_by_type' => $this->changed_by_type,
            'note' => $this->note,
            'created_at' => $this->created_at,
            'changer' => $this->whenLoaded('changer', function () {
                return [
                    'id' => $this->changer->id,
                    'name' => $this->changer->name,
                    'role' => $this->changer->role,
                    'email' => $this->changer->email,
                    'phone' => $this->changer->phone,
                    'changer_type' => $this->changed_by_type,
                    'avatar' => $this->changer->avatar,
                    'avatar_url' => $this->changer->avatar ? \Storage::disk('public')->url('users/' . $this->changer->avatar) : null,
                    'gender' => $this->changer->gender,
                    'birthdate' => $this->changer->birthdate,
                    'blood_type' => $this->changer->blood_type,
                    'allergy' => $this->changer->allergy,
                    'chronic_diseases' => $this->changer->chronic_diseases,
                    'is_active' => $this->changer->is_active,
                    'device_token' => $this->changer->device_token,
                    'device_type' => $this->changer->device_type,
                ];
            }),
        ];
    }
}
