<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'article_id' => $this->article_id,
            'user' => [
                'id' => $this->user->id ?? null,
                'name' => $this->user->name ?? null,
                'avatar' => $this->user->avatar ?? null,
            ],
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'children' => ArticleCommentResource::collection($this->whenLoaded('children')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
