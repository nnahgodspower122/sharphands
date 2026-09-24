<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Plumbing', 'Electrical', 'Cleaning', 'Carpentry']);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numerify('##'),
            'description' => fake()->sentence(),
            'icon' => 'wrench',
            'base_amount' => fake()->numberBetween(5000, 25000),
        ];
    }
}
