<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    public function definition(): array
    {
        $service = Service::factory();

        return [
            'user_id' => User::factory(),
            'worker_id' => Worker::factory(),
            'service_id' => $service,
            'status' => BookingStatus::Pending,
            'amount' => fake()->numberBetween(5000, 30000),
            'latitude' => 6.5244,
            'longitude' => 3.3792,
        ];
    }
}
