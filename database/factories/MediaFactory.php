<?php

namespace Database\Factories;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Defaults to being attached to a post; use `->for($model, 'mediable')`
     * to attach it to a user, tag or section instead.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'mediable_type' => Post::class,
            'mediable_id' => Post::factory(),
            'url' => 'media/' . fake()->uuid() . '.webp',
            'mime' => 'image',
        ];
    }

    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => 'media/' . fake()->uuid() . '.mp4',
            'mime' => 'video',
        ]);
    }
}
