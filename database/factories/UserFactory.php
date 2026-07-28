<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * A user who may manage posts, tags, topics, sections and videos.
     */
    public function blogger(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'blogger',
        ]);
    }

    /**
     * Give the user an avatar, which the nav bar and profile page render.
     */
    public function withMedia(): static
    {
        return $this->afterCreating(function (User $user): void {
            $user->media()->create([
                'url' => 'media/' . fake()->uuid() . '.webp',
                'mime' => 'image',
            ]);
        });
    }
}
