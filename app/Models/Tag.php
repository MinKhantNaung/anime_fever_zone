<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Str;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'body',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag): void {
            $tag->slug = Str::slug($tag->name);
        });

        static::updating(function ($tag): void {
            $tag->slug = Str::slug($tag->name);
        });
    }

    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, PostTag::class);
    }

    /** Database Logic */
    public function getAllByName()
    {
        return $this->query()
            ->select('id', 'name')
            ->get();
    }
}
