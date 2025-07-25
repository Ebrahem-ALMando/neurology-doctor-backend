<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'phone'             => $this->phone,
            'role'              => $this->role,
            'avatar'            => $this->avatar,
            'avatar_url'        => $this->avatar ? Storage::disk('public')->url('users/' . $this->avatar) : null,
            'gender'            => $this->gender,
            'birthdate'         => $this->birthdate,
            'blood_type'        => $this->blood_type,
            'allergy'           => $this->allergy,
            'chronic_diseases'  => $this->chronic_diseases,
            'is_active'         => $this->is_active,
            'device_token'      => $this->device_token,
            'device_type'       => $this->device_type,
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
        ];
    }
}
