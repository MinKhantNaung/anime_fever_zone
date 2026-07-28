<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * The slug is generated from the heading by the model's boot hooks.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'topic_id' => Topic::factory(),
            'heading' => rtrim(fake()->unique()->sentence(), '.'),
            'body' => fake()->paragraphs(3, true),
            'is_feature' => false,
            'is_publish' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_publish' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_publish' => false,
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_feature' => true,
        ]);
    }
}
