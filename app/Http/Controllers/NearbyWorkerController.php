<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\NearbyWorkerService;
use Illuminate\Http\Request;

class NearbyWorkerController extends Controller
{
    public function index(Request $request, NearbyWorkerService $nearbyWorkers)
    {
        $service = Service::query()->findOrFail($request->integer('service_id'));
        $latitude = $request->float('lat', 6.5244);
        $longitude = $request->float('lng', 3.3792);

        return view('workers.nearby', [
            'service' => $service,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'workers' => $nearbyWorkers->find($service->id, $latitude, $longitude),
        ]);
    }
}
