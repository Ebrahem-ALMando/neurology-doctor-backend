<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ConsultationAttachmentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'file_name' => $this->file_name,
            'file_type' => $this->file_type,
            'file_url' => $this->file_name ? Storage::disk('public')->url($this->file_path ?? ('consultations/attachments/' . $this->file_name)) : null,
            'original_name' => $this->original_name,
        ];
    }
}
