<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

final class Video extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'youtube_url',
        'youtube_id',
        'is_publish',
    ];

    protected $casts = [
        'is_publish' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($video): void {
            $video->slug = Str::slug($video->title);
        });

        static::updating(function ($video): void {
            if ($video->isDirty('title')) {
                $video->slug = Str::slug($video->title);
            }
        });
    }

    /**
     * Get YouTube thumbnail URL
     *
     * @param string $quality - maxresdefault, hqdefault, mqdefault, sddefault
     * @return string
     */
    public function getThumbnailUrl($quality = 'maxresdefault'): string
    {
        if (!$this->youtube_id) {
            return '';
        }

        return "https://img.youtube.com/vi/{$this->youtube_id}/{$quality}.jpg";
    }
}
