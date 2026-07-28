<?php

namespace Database\Factories;

use App\Models\SiteSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SiteSetting>
 */
class SiteSettingFactory extends Factory
{
    protected $model = SiteSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email_verify_status' => false,
        ];
    }

    public function verifyingEmails(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verify_status' => true,
        ]);
    }
}
