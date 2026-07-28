<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Section>
 */
class SectionFactory extends Factory
{
    protected $model = Section::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'heading' => fake()->sentence(),
            'body' => fake()->paragraph(),
        ];
    }

    public function withoutHeading(): static
    {
        return $this->state(fn (array $attributes) => [
            'heading' => null,
        ]);
    }
}
