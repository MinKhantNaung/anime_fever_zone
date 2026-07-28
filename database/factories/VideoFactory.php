<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Video does not use the HasFactory trait, so there is no `Video::factory()`.
 * Build videos with `VideoFactory::new()` instead.
 *
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * The slug is generated from the title by the model's boot hooks.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $youtubeId = Str::random(11);

        return [
            'title' => rtrim(fake()->unique()->sentence(), '.'),
            'description' => fake()->paragraph(),
            'youtube_url' => 'https://www.youtube.com/watch?v=' . $youtubeId,
            'youtube_id' => $youtubeId,
            'is_publish' => false,
            'is_trending' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_publish' => true,
        ]);
    }

    public function trending(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_trending' => true,
        ]);
    }
}
