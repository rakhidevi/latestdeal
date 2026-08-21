<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'slug',
        'photo_path',
        'bio',
        'expertise',
        'social_links',
    ];

    protected $casts = [
        'social_links' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getPhotoUrlAttribute(): string
    {
        $path = $this->attributes['photo_path'] ?? '';
        if (empty($path)) {
            // Return a default placeholder avatar
            return asset('images/default-avatar.png');
        }

        if (filter_var($path, FILTER_VALIDATE_URL) || \Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) {
            return preg_replace('/^http:/i', 'https:', $path);
        }

        $cleanPath = ltrim($path, '/');
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        return asset('storage/' . $cleanPath);
    }
}
