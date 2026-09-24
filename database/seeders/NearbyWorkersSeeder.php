<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\WorkerStatus;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Database\Seeder;

class NearbyWorkersSeeder extends Seeder
{
    /**
     * Place verified, available workers in a tight ring around a map center.
     */
    public function run(?float $latitude = null, ?float $longitude = null): void
    {
        $latitude ??= (float) env('SEED_NEARBY_LAT', 4.8156);
        $longitude ??= (float) env('SEED_NEARBY_LNG', 7.0498);

        $services = Service::query()->get()->keyBy('slug');

        if ($services->isEmpty()) {
            $this->command?->warn('No services found. Run the main seeder first.');

            return;
        }

        $clusters = [
            ['lat' => $latitude, 'lng' => $longitude],
        ];

        $graLat = 4.8297;
        $graLng = 7.0139;

        if (abs($latitude - $graLat) > 0.01 || abs($longitude - $graLng) > 0.01) {
            $clusters[] = ['lat' => $graLat, 'lng' => $graLng];
        }

        $people = [
            'plumbing' => [
                ['Tunde Adewale', 'tunde.plumber@example.com'],
                ['Kemi Olatunji', 'kemi.plumber@example.com'],
                ['Femi Ajayi', 'femi.plumber@example.com'],
                ['Hassan Bello', 'hassan.plumber@example.com'],
            ],
            'electrical' => [
                ['Yemi Okonkwo', 'yemi.electric@example.com'],
                ['Rita Eze', 'rita.electric@example.com'],
                ['Kunle Balogun', 'kunle.electric@example.com'],
                ['Adaobi Nwosu', 'adaobi.electric@example.com'],
            ],
            'cleaning' => [
                ['Blessing Udo', 'blessing.clean@example.com'],
                ['Chiamaka Obi', 'chiamaka.clean@example.com'],
                ['Samuel Idowu', 'samuel.clean@example.com'],
                ['Halima Sani', 'halima.clean@example.com'],
            ],
            'carpentry' => [
                ['Peter Okeke', 'peter.carpenter@example.com'],
                ['Lola Adekunle', 'lola.carpenter@example.com'],
                ['Musa Abdullahi', 'musa.carpenter@example.com'],
                ['Ifeanyi Chukwu', 'ifeanyi.carpenter@example.com'],
            ],
            'tailoring' => [
                ['Nneka Uche', 'nneka.tailor@example.com'],
                ['Bisi Afolabi', 'bisi.tailor@example.com'],
                ['Ibrahim Sule', 'ibrahim.tailor@example.com'],
                ['Adaeze Mbah', 'adaeze.tailor@example.com'],
            ],
        ];

        $offsets = [
            [0.004, 0.003],
            [-0.003, 0.006],
            [0.007, -0.004],
            [-0.006, -0.003],
        ];

        foreach ($clusters as $clusterIndex => $cluster) {
            foreach ($people as $slug => $workers) {
                $service = $services->get($slug);

                if (! $service) {
                    continue;
                }

                foreach ($workers as $index => [$name, $email]) {
                    $seedEmail = $clusterIndex === 0
                        ? $email
                        : str_replace('@example.com', '.gra@example.com', $email);

                    $offset = $offsets[$index % count($offsets)];
                    $jitter = $clusterIndex * 0.002;

                    $user = User::query()->updateOrCreate(
                        ['email' => $seedEmail],
                        [
                            'name' => $name,
                            'phone' => fake()->numerify('+23480#######'),
                            'role' => UserRole::Worker,
                            'password' => 'password',
                        ],
                    );

                    Worker::query()->updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'service_id' => $service->id,
                            'name' => $name,
                            'phone' => $user->phone,
                            'latitude' => round($cluster['lat'] + $offset[0] + $jitter, 6),
                            'longitude' => round($cluster['lng'] + $offset[1] - $jitter, 6),
                            'status' => WorkerStatus::Available,
                            'verified' => true,
                            'applied_at' => now()->subDays(8),
                        ],
                    );
                }
            }
        }

        Worker::query()
            ->whereBetween('latitude', [6.3, 6.7])
            ->get()
            ->each(function (Worker $worker, int $index) use ($latitude, $longitude, $offsets) {
                $offset = $offsets[$index % count($offsets)];
                $worker->update([
                    'latitude' => round($latitude + $offset[0], 6),
                    'longitude' => round($longitude + $offset[1], 6),
                    'status' => WorkerStatus::Available,
                ]);
            });

        Worker::query()->where('verified', true)->update(['status' => WorkerStatus::Available->value]);
    }
}
