<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Enums\WorkerStatus;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Worker>
 */
class WorkerFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->worker()->create();

        return [
            'user_id' => $user->id,
            'service_id' => Service::factory(),
            'name' => $user->name,
            'phone' => $user->phone ?? fake()->numerify('+23480#######'),
            'latitude' => fake()->latitude(6.4, 6.6),
            'longitude' => fake()->longitude(3.3, 3.5),
            'status' => WorkerStatus::Available,
            'verified' => true,
            'applied_at' => now()->subDays(fake()->numberBetween(1, 20)),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified' => false,
        ]);
    }
}
