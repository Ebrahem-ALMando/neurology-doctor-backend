<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'category_id',
        'title',
        'short_description',
        'content',
        'views_count',
        'is_published',
        'published_at',
    ];

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function category()
    {
        return $this->belongsTo(ArticleCategory::class, 'category_id');
    }

    public function images()
    {
        return $this->hasMany(ArticleImage::class);
    }

    public function comments()
    {
        return $this->hasMany(ArticleComment::class);
    }
}
