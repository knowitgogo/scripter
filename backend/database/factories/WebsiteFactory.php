<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WebsiteStatus;
use App\Models\User;
use App\Models\Website;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Website>
 */
class WebsiteFactory extends Factory
{
    protected $model = Website::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company().' Site',
            'url' => fake()->unique()->url(),
            'status' => WebsiteStatus::Active,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => WebsiteStatus::Inactive,
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => WebsiteStatus::Suspended,
        ]);
    }
}
