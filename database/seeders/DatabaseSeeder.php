<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\UserRole;
use App\Enums\WorkerStatus;
use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $services = collect([
            ['name' => 'Plumbing', 'slug' => 'plumbing', 'icon' => 'droplet', 'base_amount' => 8500, 'description' => 'For leaks, installations, and water system repairs done professionally.'],
            ['name' => 'Electrical', 'slug' => 'electrical', 'icon' => 'zap', 'base_amount' => 10000, 'description' => 'Expert wiring, appliance repair, and power management for your home.'],
            ['name' => 'Cleaning', 'slug' => 'cleaning', 'icon' => 'sparkles', 'base_amount' => 12000, 'description' => 'Home and office cleaning, delivered to a professional standard.'],
            ['name' => 'Carpentry', 'slug' => 'carpentry', 'icon' => 'hammer', 'base_amount' => 15000, 'description' => 'Custom furniture, repairs, and woodwork you can count on.'],
            ['name' => 'Tailoring', 'slug' => 'tailoring', 'icon' => 'machine', 'base_amount' => 7000, 'description' => 'Fitting, alterations, and custom sewing from trusted local tailors.'],
        ])->map(fn (array $service) => Service::query()->updateOrCreate(
            ['slug' => $service['slug']],
            $service,
        ));

        User::query()->updateOrCreate(
            ['email' => 'admin@sharphand.ng'],
            [
                'name' => 'SharpHand Admin',
                'phone' => '+2348001234567',
                'role' => UserRole::Admin,
                'password' => 'password',
            ],
        );

        $customers = [
            ['name' => 'Emeka Obi', 'email' => 'emeka.o@example.com'],
            ['name' => 'Amara Okoro', 'email' => 'amara.k@example.com'],
            ['name' => 'Tunde Bakare', 'email' => 't.bakare@example.com'],
            ['name' => 'Fatima Yusuf', 'email' => 'fatima.y@example.com'],
            ['name' => 'John Okafor', 'email' => 'j.okafor@example.com'],
            ['name' => 'Amina Balogun', 'email' => 'amina.b@example.com'],
        ];

        $users = collect($customers)->map(fn (array $customer) => User::query()->updateOrCreate(
            ['email' => $customer['email']],
            [
                'name' => $customer['name'],
                'phone' => fake()->numerify('+23480#######'),
                'role' => UserRole::User,
                'password' => 'password',
            ],
        ));

        $workerSeeds = [
            ['name' => 'Sunday Adebayo', 'email' => 'sunday.a@example.com', 'service' => 'electrical', 'verified' => true, 'lat' => 4.8156, 'lng' => 7.0498],
            ['name' => 'Chidi Mensah', 'email' => 'chidi.m@example.com', 'service' => 'plumbing', 'verified' => true, 'lat' => 4.8245, 'lng' => 7.0330],
            ['name' => 'Grace Akpan', 'email' => 'grace.a@example.com', 'service' => 'cleaning', 'verified' => true, 'lat' => 4.8065, 'lng' => 7.0472],
            ['name' => 'Ibrahim Lawal', 'email' => 'ibrahim.l@example.com', 'service' => 'carpentry', 'verified' => true, 'lat' => 4.8500, 'lng' => 7.0200],
            ['name' => 'Mercy Johnson', 'email' => 'mercy.j@example.com', 'service' => 'cleaning', 'verified' => true, 'lat' => 4.8300, 'lng' => 7.0600],
            ['name' => 'Seyi Makinde', 'email' => 'seyi.m@example.com', 'service' => 'plumbing', 'verified' => false, 'lat' => 4.8000, 'lng' => 7.0300],
            ['name' => 'Ngozi Iweala', 'email' => 'ngozi.i@example.com', 'service' => 'cleaning', 'verified' => false, 'lat' => 4.8400, 'lng' => 7.0400],
            ['name' => 'Babatunde Fashola', 'email' => 'babatunde.f@example.com', 'service' => 'electrical', 'verified' => false, 'lat' => 4.7900, 'lng' => 7.0550],
        ];

        $workers = collect($workerSeeds)->mapWithKeys(function (array $seed) use ($services) {
            $user = User::query()->updateOrCreate(
                ['email' => $seed['email']],
                [
                    'name' => $seed['name'],
                    'phone' => fake()->numerify('+23480#######'),
                    'role' => UserRole::Worker,
                    'password' => 'password',
                ],
            );

            $worker = Worker::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'service_id' => $services->firstWhere('slug', $seed['service'])->id,
                    'name' => $seed['name'],
                    'phone' => $user->phone,
                    'latitude' => $seed['lat'],
                    'longitude' => $seed['lng'],
                    'status' => WorkerStatus::Available,
                    'verified' => $seed['verified'],
                    'applied_at' => $seed['verified'] ? now()->subDays(10) : now()->subHours(rand(2, 30)),
                ],
            );

            return [$seed['email'] => $worker];
        });

        $demoBookings = [
            ['user' => 'emeka.o@example.com', 'worker' => 'sunday.a@example.com', 'amount' => 15000, 'status' => BookingStatus::Completed, 'days' => 30],
            ['user' => 'amara.k@example.com', 'worker' => 'chidi.m@example.com', 'amount' => 8500, 'status' => BookingStatus::InProgress, 'days' => 1],
            ['user' => 't.bakare@example.com', 'worker' => 'grace.a@example.com', 'amount' => 12000, 'status' => BookingStatus::Pending, 'days' => 0],
            ['user' => 'fatima.y@example.com', 'worker' => 'ibrahim.l@example.com', 'amount' => 22000, 'status' => BookingStatus::Completed, 'days' => 31],
            ['user' => 'j.okafor@example.com', 'worker' => 'mercy.j@example.com', 'amount' => 30000, 'status' => BookingStatus::Cancelled, 'days' => 32],
        ];

        foreach ($demoBookings as $row) {
            $user = $users->firstWhere('email', $row['user']);
            $worker = $workers[$row['worker']];

            Booking::query()->updateOrCreate(
                [
                    'user_id' => $user->id,
                    'worker_id' => $worker->id,
                    'amount' => $row['amount'],
                ],
                [
                    'service_id' => $worker->service_id,
                    'status' => $row['status'],
                    'latitude' => 4.8156,
                    'longitude' => 7.0498,
                    'created_at' => now()->subDays($row['days']),
                    'updated_at' => now()->subDays($row['days']),
                ],
            );
        }

        $this->call(NearbyWorkersSeeder::class);
    }
}
