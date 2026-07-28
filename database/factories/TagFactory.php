<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * The slug is generated from the name by the model's boot hooks.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'body' => fake()->paragraph(),
        ];
    }

    /**
     * Attach a banner image, which the tag show page dereferences directly.
     */
    public function withMedia(): static
    {
        return $this->afterCreating(function (Tag $tag): void {
            $tag->media()->create([
                'url' => 'media/' . fake()->uuid() . '.webp',
                'mime' => 'image',
            ]);
        });
    }
}
