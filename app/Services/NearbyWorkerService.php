<?php

namespace App\Services;

use App\Enums\WorkerStatus;
use App\Models\Worker;
use App\Support\Geo;
use Illuminate\Support\Collection;

class NearbyWorkerService
{
    public function find(?int $serviceId, float $latitude, float $longitude, float $radiusKm = 20): Collection
    {
        return Worker::query()
            ->with(['service'])
            ->when($serviceId, fn ($query) => $query->where('service_id', $serviceId))
            ->where('verified', true)
            ->where('status', WorkerStatus::Available)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->map(function (Worker $worker) use ($latitude, $longitude) {
                $worker->distance_km = round(Geo::distanceKm(
                    $latitude,
                    $longitude,
                    (float) $worker->latitude,
                    (float) $worker->longitude,
                ), 2);
                $worker->syncOriginal();

                return $worker;
            })
            ->filter(fn (Worker $worker) => $worker->distance_km <= $radiusKm)
            ->sortBy('distance_km')
            ->values();
    }

    public function nearest(int $serviceId, float $latitude, float $longitude, float $radiusKm = 20): ?Worker
    {
        return $this->find($serviceId, $latitude, $longitude, $radiusKm)->first();
    }

    public function countsByService(float $latitude, float $longitude, float $radiusKm = 20): Collection
    {
        return Worker::query()
            ->where('verified', true)
            ->where('status', WorkerStatus::Available)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(fn (Worker $worker) => Geo::distanceKm(
                $latitude,
                $longitude,
                (float) $worker->latitude,
                (float) $worker->longitude,
            ) <= $radiusKm)
            ->countBy('service_id');
    }

    public function publicList(?int $serviceId, float $latitude, float $longitude, float $radiusKm = 20): Collection
    {
        return $this->find($serviceId, $latitude, $longitude, $radiusKm)
            ->map(function (Worker $worker) {
                return [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'service_id' => $worker->service_id,
                    'service' => $worker->service,
                    'latitude' => $worker->latitude,
                    'longitude' => $worker->longitude,
                    'status' => $worker->status,
                    'distance_km' => $worker->distance_km,
                    'initials' => $worker->initials(),
                ];
            });
    }
}
