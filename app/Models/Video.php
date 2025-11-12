<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class Video extends Model
{
    protected $fillable = [
        'title',
        'youtube_url',
        'youtube_id',
        'is_publish',
    ];

    protected $casts = [
        'is_publish' => 'boolean',
    ];

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
