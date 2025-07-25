<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'doctor' => $this->doctor ? [
                'id' => $this->doctor->id,
                'name' => $this->doctor->name,
                'avatar' => $this->doctor->avatar,
            ] : null,
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'title' => $this->title,
            'short_description' => $this->short_description,
            'content' => $this->content,
            'views_count' => $this->views_count,
            'is_published' => (bool) $this->is_published,
            'published_at' => $this->published_at,
            'images' => ArticleImageResource::collection($this->whenLoaded('images')),
            'comments_count' => $this->comments()->count(),
            'comments' => ArticleCommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
