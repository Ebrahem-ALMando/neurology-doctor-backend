<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArticleImageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'image_name' => $this->image_name,
            'folder' => $this->folder,
            'is_cover' => (bool) $this->is_cover,
            'caption' => $this->caption,
            'image_url' => Storage::disk('uploads')->url($this->folder . '/' . $this->image_name),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
