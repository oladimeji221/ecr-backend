<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'meta_robot',
        'category_id',
        'user_id',
        'status',
        'canonical_url',
        'custom_url',
        'json_ld',
        'og_title',
        'og_description',
        'og_image',
    ];

    protected $appends = [
        'view_count',
        'dislike_count'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($blog) {
            $blog->slug = Str::slug($blog->title);
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title')) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }

    public function getImageAttribute($value)
    {
        if (!$value) {
            return null;
        }
        
        // If it's already a Cloudinary URL (starts with http:// or https://), return it directly
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        
        // Otherwise, it's a local storage path, use the default Storage URL
        return asset('storage/' . $value);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function dislikes()
    {
        return $this->hasMany(Dislike::class);
    }

    public function views()
    {
        return $this->hasMany(BlogView::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getViewCountAttribute()
    {
        return $this->views()->count();
    }

    public function getDislikeCountAttribute()
    {
        return $this->dislikes()->count();
    }
}
