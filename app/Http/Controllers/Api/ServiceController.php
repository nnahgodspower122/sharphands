<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\NearbyWorkerService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Service::query()->orderBy('name')->get(),
        ]);
    }

    public function nearby(Request $request, NearbyWorkerService $nearbyWorkers)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $counts = $nearbyWorkers->countsByService((float) $data['lat'], (float) $data['lng']);

        return response()->json([
            'data' => Service::query()->orderBy('name')->get()->map(fn (Service $service) => [
                ...$service->toArray(),
                'nearby_count' => (int) ($counts[$service->id] ?? 0),
            ]),
        ]);
    }
}
