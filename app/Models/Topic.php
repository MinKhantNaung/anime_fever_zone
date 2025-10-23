<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($topic): void {
            $topic->slug = Str::slug($topic->name);
        });

        static::updating(function ($topic): void {
            $topic->slug = Str::slug($topic->name);
        });
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /** Database Logic */
    public function getAllByName()
    {
        return $this->query()
            ->select('id', 'name', 'slug')
            ->get();
    }
}
