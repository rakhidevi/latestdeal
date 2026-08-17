<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_REVIEW = 'review';
    const STATUS_PUBLISHED = 'published';

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'content',
        'status',
        'author_id',
        'category_id',
        'featured_image',
        'seo_metadata',
        'published_at'
    ];

    protected $casts = [
        'seo_metadata' => 'array',
        'published_at' => 'datetime'
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED && 
               $this->published_at !== null && 
               $this->published_at->isPast();
    }
}
